<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ModulComponent;
use App\Models\Project;
use App\Models\PartComponent;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class KomponenTable extends Component
{
    public $moduls = [];
    public $groupedComponents = [];
    public $partComponentsData = [];
    public $columns;
    public $fieldMapping;
    public $dataValidationCol;
    public $dataValMap;
    public $componentTypes = [];
    public $componentOptions = [];
    public $recordId;
    public $allSpecs = [
        'product_spesification' => [],
        'material_thickness_spesification' => [],
        'coating_spesification' => [],
        'komp_anodize_spesification' => [],
        'alu_frame_spesification' => [],
        'hinges_spesification' => [],
        'rail_spesification' => [],
        'glass_spesification' => [],
    ];
    protected $listeners = ['typeChanged'];

    public function typeChanged($type)
    {
        $this->componentOptions = PartComponent::query()
            ->get()
            ->map(function ($component) {
                $data = $this->parsePartComponentData($component);
                return [
                    'value' => $data['name'] ?? '',
                    'label' => $data['name'] ?? '',
                    'data' => $data
                ];
            })
            ->filter(function ($option) use ($type) {
                return isset($option['data']['code']) && $option['data']['code'] === $type;
            })
            ->values()
            ->toArray();

        $this->emit('updateComponentOptions', $this->componentOptions);
    }

    protected function parsePartComponentData($partComponent)
    {
        $data = $partComponent->toArray();

        if (!empty($data['part_component']) && is_string($data['part_component'])) {
            try {
                $jsonString = trim($data['part_component'], '"');
                $decoded = json_decode($jsonString, true);

                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $allComponents = [];
                    $primaryComponent = null;

                    // Ekstrak semua komponen
                    foreach ($decoded as $item) {
                        if (isset($item['data']) && is_array($item['data'])) {
                            // Gunakan komponen pertama sebagai primary
                            if ($primaryComponent === null) {
                                $primaryComponent = $item['data'];
                            }
                            $allComponents[] = $item['data'];
                        }
                    }
                    $data = $allComponents;
                }
            } catch (\Exception $e) {
                logger()->error('Failed to parse part_component: ' . $e->getMessage());
            }
        }
        return $data;
    }
    public function mount($moduls, $recordId = null)
    {
        $this->columns = config('breakdown_fields.breakdown_col');
        $this->fieldMapping = config('breakdown_fields.fields_mapping');
        $this->dataValidationCol = config("breakdown_fields.data_validation_col");
        $this->dataValMap = config("breakdown_fields.data_val_map");
        $this->recordId = $recordId;
        $this->moduls = $moduls ?? [];
        $this->loadInitialData();
        $this->loadSpecData();
    }

    protected function loadInitialData()
    {
        $this->loadDropdownData();
        $this->loadGroupedComponents();
        $this->loadPartComponentData();
    }

    public function loadDropdownData()
    {
        $this->componentTypes = PartComponent::query()
            ->get()
            ->map(function ($part) {
                $data = $this->parsePartComponentData($part);
                return $data['code'] ?? null;
            })
            ->filter()
            ->unique()
            ->map(function ($code) {
                return [
                    'value' => $code,
                    'label' => $code
                ];
            })
            ->values()
            ->toArray();

        $this->componentOptions = PartComponent::query()
            ->get()
            ->map(function ($part) {
                $data = $this->parsePartComponentData($part);
                return [
                    'value' => $data['name'] ?? '',
                    'label' => $data['name'] ?? '',
                    'data' => $data
                ];
            })
            ->filter(function ($option) {
                return !empty($option['value']);
            })
            ->values()
            ->toArray();
    }

    public function loadGroupedComponents()
    {
        $this->groupedComponents = ['array' => []];
        $decodedModuls = $this->getDecodedModuls();

        if ($this->recordId) {
            $this->loadComponentsFromProject($decodedModuls);
        }

        $this->loadMissingModuls($decodedModuls);
        $this->ensureAllModulsPresent($decodedModuls);
    }

    protected function getDecodedModuls()
    {
        return array_map(function ($item) {
            return is_string($item) ? json_decode('"' . $item . '"') : $item;
        }, $this->moduls);
    }

    protected function loadComponentsFromProject($decodedModuls)
    {
        $project = Project::find($this->recordId);
        if (!$project || empty($project->modul_breakdown)) {
            return;
        }

        $modulBreakdown = is_array($project->modul_breakdown)
            ? $project->modul_breakdown
            : json_decode($project->modul_breakdown, true);

        if (!is_array($modulBreakdown)) {
            return;
        }

        foreach ($modulBreakdown as $item) {
            if (!isset($item['modul']['nama_modul'])) {
                continue;
            }

            $modulName = $item['modul']['nama_modul'];
            if (!in_array($modulName, $decodedModuls)) {
                continue;
            }

            $components = $item['components'] ?? [];
            $processedComponents = $this->processComponents($components);

            $this->groupedComponents['array'][] = [
                'modul' => $item['modul'],
                'component' => $processedComponents,
                'isFilled' => true,
            ];
        }
    }

    protected function loadMissingModuls($decodedModuls)
    {
        $existingModuls = array_column(array_column($this->groupedComponents['array'], 'modul'), 'nama_modul');
        $missingModuls = array_diff($decodedModuls, $existingModuls);

        if (empty($missingModuls)) {
            return;
        }

        $modulComponents = ModulComponent::whereIn('modul', $missingModuls)->get();

        foreach ($modulComponents as $modulComponent) {
            $componentList = $this->parseComponentData($modulComponent->component);
            $processedComponents = $this->processComponents($componentList ?? []);

            $this->groupedComponents['array'][] = [
                'modul' => ['nama_modul' => $modulComponent->modul],
                'component' => $processedComponents,
                'isFilled' => false
            ];
        }
    }

    protected function ensureAllModulsPresent($decodedModuls)
    {
        foreach ($decodedModuls as $modul) {
            $found = collect($this->groupedComponents['array'])
                ->contains(function ($item) use ($modul) {
                    return $item['modul']['nama_modul'] === $modul;
                });

            if (!$found) {
                $this->groupedComponents['array'][] = [
                    'modul' => ['nama_modul' => $modul],
                    'component' => [],
                    'isFilled' => false
                ];
            }
        }
    }

    protected function processComponents($components)
    {
        if (!is_array($components)) {
            return [];
        }

        return array_map(function ($comp) {
            if (!isset($comp['component'])) {
                return $comp;
            }

            $partComponent = $this->findPartComponentByName($comp['component']);
            return $partComponent ? array_merge($comp, $partComponent) : $comp;
        }, $components);
    }

    protected function findPartComponentByName($name)
    {
        return PartComponent::query()
            ->get()
            ->map(function ($component) {
                return $this->parsePartComponentData($component);
            })
            ->first(function ($data) use ($name) {
                return $this->componentMatchesName($data, $name);
            });
    }

    protected function componentMatchesName($componentData, $name)
    {
        if (isset($componentData['name']) && $componentData['name'] === $name) {
            return true;
        }

        if (!empty($componentData['part_component'])) {
            try {
                $jsonString = trim($componentData['part_component'], '"');
                $decoded = json_decode($jsonString, true);

                return is_array($decoded) &&
                    !empty($decoded[0]['data']['name']) &&
                    $decoded[0]['data']['name'] === $name;
            } catch (\Exception $e) {
                return false;
            }
        }

        return false;
    }

    protected function parseComponentData($componentData)
    {
        if (is_string($componentData)) {
            $decoded = json_decode($componentData, true);
            return json_last_error() === JSON_ERROR_NONE ? $decoded : null;
        }

        return is_array($componentData) ? $componentData : null;
    }

    public function updatedGroupedComponents($value, $key)
    {
        [$modul, $index, $field] = explode('.', $key);

        $modulComponent = ModulComponent::where('modul', $modul)->first();
        if (!$modulComponent) {
            return;
        }

        $componentList = $this->parseComponentData($modulComponent->component) ?? [];
        $componentList[$index][$field] = $value;

        if ($field === 'component') {
            $partComponent = $this->findPartComponentByName($value);
            if ($partComponent) {
                $componentList[$index] = array_merge($componentList[$index], $partComponent);
            }
        }

        $modulComponent->component = json_encode($componentList);
        $modulComponent->save();
        $this->loadGroupedComponents();
    }

    public function loadPartComponentData()
    {
        $decodedModuls = $this->getDecodedModuls();

        // First get all component data from ModulComponent
        $modulComponentsData = ModulComponent::whereIn('modul', $decodedModuls)
            ->get()
            ->flatMap(function ($modulComponent) {
                $components = $this->parseComponentData($modulComponent->component);
                return is_array($components) ? $components : [];
            });

        // Extract unique component names from modul components
        $componentNames = $modulComponentsData
            ->pluck('component')
            ->filter()
            ->unique()
            ->values();

        // Get all part components and parse their data
        $allPartComponents = PartComponent::all()
            ->map(function ($component) {
                return $this->parsePartComponentData($component);
            })
            ->collapse();

        // Filter part components that match the names from modul components
        $this->partComponentsData = $allPartComponents
            ->filter(function ($partComponent) use ($componentNames) {
                $name = $partComponent['name'] ?? null;
                return $componentNames->contains(function ($componentName) use ($name) {
                    return $componentName === $name;
                });
            })
            ->unique('name')
            ->values()
            ->toArray();
    }

    public function save(Request $request)
    {
        $validated = $request->validate([
            'breakdown_modul' => 'required|array',
            'columns' => 'required|array'
        ]);

        try {
            $project = new Project();
            $project->breakdown_modul = json_encode($validated['breakdown_modul']);
            $project->columns = json_encode($validated['columns']);
            $project->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Data berhasil disimpan',
                'data' => $project
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'modul_breakdown' => 'required|array',
            'columns' => 'required|array',
            'recordId' => 'required|integer'
        ]);

        try {
            $project = Project::findOrFail($validated['recordId']);
            $project->modul_breakdown = json_encode($validated['modul_breakdown']);
            $project->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Data berhasil diupdate',
                'data' => $project
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengupdate data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getSpecData()
    {
        // Langsung kembalikan variabel allSpecs
        return $this->allSpecs;
    }

    public function loadSpecData()
    {
        if (!$this->recordId) {
            return;
        }

        $project = Project::find($this->recordId);
        if (!$project) {
            return;
        }

        $this->allSpecs = [
            'product_spesification' => $this->parseSpecData($project->product_spesification),
            'material_thickness_spesification' => $this->parseSpecData($project->material_thickness_spesification),
            'coating_spesification' => $this->parseSpecData($project->coating_spesification),
            'komp_anodize_spesification' => $this->parseSpecData($project->komp_anodize_spesification),
            'alu_frame_spesification' => $this->parseSpecData($project->alu_frame_spesification),
            'hinges_spesification' => $this->parseSpecData($project->hinges_spesification),
            'rail_spesification' => $this->parseSpecData($project->rail_spesification),
            'glass_spesification' => $this->parseSpecData($project->glass_spesification),
        ];
    }

    protected function parseSpecData($data)
    {
        if (is_string($data)) {
            $decoded = json_decode($data, true);
            return json_last_error() === JSON_ERROR_NONE ? $decoded : [];
        }
        return is_array($data) ? $data : [];
    }

    public function render()
    {
        return view('livewire.komponen-table');
    }
}
