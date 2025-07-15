<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Modul;
use App\Models\ModulComponent;
use App\Models\Project;
use App\Models\PartComponent;
use App\Models\RemovablePart;
use Illuminate\Http\Request;

class KomponenTable extends Component
{
    public $moduls = [];
    public $groupedComponents = [];
    public $modulList = [];
    public $partComponentsData = [];
    public $columns;
    public $allModuls = [];
    public $modulsByNip = [];
    public $allParts = [];
    public $definedNames = [];
    public $fieldMapping;
    public $dataValidationCol;
    public $dataValMap;
    public $componentTypes = [];
    public $componentOptions = [];
    public $recordId;
    public $nip;

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

    public function render()
    {
        return view('livewire.komponen-table');
    }

    public function mount($moduls, $recordId = null)
    {
        $this->columns = config('breakdown_fields.breakdown_col');
        $this->fieldMapping = config('breakdown_fields.fields_mapping');
        $this->dataValidationCol = config("breakdown_fields.data_validation_col");
        $this->dataValMap = config("breakdown_fields.data_val_map");
        $this->recordId = $recordId;
        $this->moduls = $moduls ?? [];
        $this->modulList = ModulComponent::all()->pluck('modul')->toArray();
        $this->loadInitialData();
        $this->loadModulsByNip($recordId);
        $this->loadSpecData();
        $this->loadDefinedNames();
        $this->loadDropdownData();
    }

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

    protected function loadInitialData()
    {
        $this->loadDropdownData();
        $this->loadGroupedComponents();
        $this->loadPartComponentData();
        $this->loadAllModuls();
        $this->loadAllRemovableParts();
    }

    public function loadDropdownData()
    {
        $parts = PartComponent::all();
        $types = [];
        $options = [];

        foreach ($parts as $part) {
            $decoded = json_decode($part->part_component, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                continue;
            }

            // Process each item in the array
            foreach ($decoded as $item) {
                // Skip if item doesn't have data
                if (!isset($item['data'])) {
                    continue;
                }

                $componentData = $item['data'];

                if (!isset($componentData['name'])) {
                    continue;
                }

                // Build component types
                if (isset($componentData['code'])) {
                    $types[$componentData['code']] = [
                        'value' => $componentData['code'],
                        'label' => $componentData['code']
                    ];
                }

                // Build component options
                $options[] = [
                    'value' => $componentData['name'],
                    'label' => $componentData['name'],
                    'data' => $componentData
                ];
            }
        }

        $this->componentTypes = array_values($types);
        $this->componentOptions = $options;
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

    // In KomponenTable.php
    public function loadUpdatedGroupedComponents(Request $request)
    {
        $this->groupedComponents = ['array' => []];
        $modul_reference = $request->input('modul_reference');

        // Validate if modul_reference exists
        if (!$modul_reference) {
            return response()->json([
                'success' => false,
                'message' => 'Modul reference is required'
            ], 400);
        }

        $decodedModuls = $modul_reference;

        if ($this->recordId) {
            $this->loadComponentsFromProject($decodedModuls);
        }

        $this->loadMissingModuls($decodedModuls);
        $this->ensureAllModulsPresent($decodedModuls);

        return response()->json([
            'success' => true,
            'groupedComponents' => $this->groupedComponents,
            'decodedModuls' => $decodedModuls
        ]);
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

        // dd($decodedModuls);
        foreach ($modulBreakdown as $item) {
            if (!isset($item['modul']['nama_modul'])) {
                continue;
            }

            // $modulName = $item['modul']['nama_modul'];
            // if (!in_array($modulName, $decodedModuls)) {
            //     continue;
            // }

            $components = $item;
            $processedComponents = $this->processComponents($components);


            $this->groupedComponents['array'][] = [
                'modul' => $item['modul'],
                'component' => [$processedComponents],
                'isFilled' => true,
            ];

            // dd($this->groupedComponents);
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

    protected function loadAllModuls()
    {
        $moduls = ModulComponent::all()->toArray();

        foreach ($moduls as $modul) {
            $componentList = $this->parseComponentData($modul['component']);
            $processedComponents = $this->processComponents($componentList ?? []);

            $this->allModuls['array'][] = [
                'modul' => ['nama_modul' => $modul['modul']],
                'component' => $processedComponents,
                'isFilled' => false
            ];
        }
    }

    protected function loadModulsByNip($recordId = null)
    {
        try {
            // Ambil NIP dari project jika recordId tersedia
            $this->nip = $recordId ? Project::findOrFail($recordId)->nip : "";

            // Jika NIP kosong, set data kosong dan return
            if (empty($this->nip)) {
                $this->modulsByNip = [
                    'array' => [],
                    'message' => 'NIP tidak tersedia'
                ];
                return;
            }

            // Query modul berdasarkan NIP
            $moduls = Modul::where('nip', $this->nip)->get();

            // Jika tidak ada modul ditemukan
            if ($moduls->isEmpty()) {
                $this->modulsByNip = [
                    'array' => [],
                    'message' => 'Tidak ada modul ditemukan untuk NIP ini'
                ];
                return;
            }

            // Proses data modul yang ditemukan
            $this->modulsByNip = [
                'array' => $moduls->map(function ($modul) {
                    return [
                        'modul' => [
                            'nama_modul' => $modul->code_cabinet,
                            'product_name' => $modul->product_name ?? null,
                            'project_name' => $modul->project_name ?? null
                            // tambahkan field lain jika diperlukan
                        ]
                    ];
                })->toArray(),
                'message' => ''
            ];
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Handle jika project tidak ditemukan
            $this->modulsByNip = [
                'array' => [],
                'message' => 'Project tidak ditemukan'
            ];
        } catch (\Exception $e) {
            // Handle error lainnya
            $this->modulsByNip = [
                'array' => [],
                'message' => 'Terjadi kesalahan saat memuat data'
            ];
        }
    }

    protected function loadAllRemovableParts()
    {
        $parts = RemovablePart::all()->toArray();

        foreach ($parts as $part) {
            $partList = $this->parseComponentData($part['component']);
            $processedParts = $this->processComponents($partList ?? []);

            $this->allParts['array'][] = [
                'part' => ['part_name' => $part['part']],
                'component' => $processedParts,
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

        $this->partComponentsData = $allPartComponents;
    }

    public function loadDefinedNames()
    {
        $allPartComponents = PartComponent::all()->toArray();

        $definedNames = $allPartComponents[0]["defined_names"];

        $this->definedNames = $definedNames;
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
            'coating_standard' => $this->parseSpecData($project->coating_standard),
            'coating_spesification' => $this->parseSpecData($project->coating_spesification),
            'komp_anodize_spesification' => $this->parseSpecData($project->komp_anodize_spesification),
            'alu_frame_spesification' => $this->parseSpecData($project->alu_frame_spesification),
            'hinges_spesification' => $this->parseSpecData($project->hinges_spesification),
            'rail_spesification' => $this->parseSpecData($project->rail_spesification),
            'glass_spesification' => $this->parseSpecData($project->glass_spesification),
            'profile_spesification' => $this->parseSpecData($project->profile_spesification),
            'size_distance_spesification' => $this->parseSpecData($project->size_distance_spesification),
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
}
