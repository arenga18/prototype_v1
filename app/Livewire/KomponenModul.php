<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\PartComponent;
use App\Models\ModulComponent;
use App\Models\RemovablePart;
use App\Models\Modul;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class KomponenModul extends Component
{
    public $modul = [];
    public $modulList = [];
    public $modulData = [];
    public $modulReference = [];
    public $partComponentsData = [];
    public $allModuls = [];
    public $allParts = [];
    public $groupedComponents = [];
    public $definedNames = [];
    public $dataValidationCol;
    public $dataValMap;
    public $columns;
    public $fieldMapping;
    public $componentTypes = [];
    public $componentOptions = [];
    public $recordId;

    protected $listeners = [
        'modulUpdated' => 'handleModulUpdate',
        'refresh' => '$refresh'
    ];

    public function mount($modul = [], $recordId = null)
    {
        $this->columns = config('breakdown_fields.breakdown_col') ?? [];
        $this->fieldMapping = config('breakdown_fields.field_mapping') ?? [];
        $this->dataValidationCol = config("breakdown_fields.data_validation_col");
        $this->dataValMap = config("breakdown_fields.data_val_map");
        $this->recordId = $recordId;

        if ($recordId) {
            $modulComponent = ModulComponent::find($recordId);
            if ($modulComponent) {
                $this->modul = $modulComponent->modul;
                $this->modulList = [$modulComponent->modul];
                $this->modulData = [$modulComponent->modul => $modulComponent->component];
            } else {
                $this->modulList = [];
            }
        } else {
            $this->modul = $modul ?? [];
            $usedModuls = ModulComponent::pluck('modul')->toArray();
            $this->modulList = Modul::whereNotIn('code_cabinet', $usedModuls)
                ->pluck('code_cabinet')
                ->toArray();
        }

        $this->modulReference = ModulComponent::all()->pluck('modul')->toArray();
        $this->loadDropdownData();
        $this->loadPartComponentData();
        $this->loadGroupedComponents();
        $this->loadDefinedNames();
        $this->loadAllModuls();
        $this->loadAllRemovableParts();
    }

    public function getModulData(Request $request)
    {
        $modul = $request->query('modul');
        $modulData = ModulComponent::where('modul', $modul)->first();

        if ($modulData) {
            return response()->json([
                'success' => true,
                'components' => $modulData->component
            ]);
        }

        return response()->json([
            'success' => false,
            'components' => null
        ]);
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

    public function updateGroupedComponent($payload)
    {
        $modul = $payload['modul'] ?? null;
        $index = $payload['index'] ?? null;
        $field = $payload['field'] ?? null;
        $value = $payload['value'] ?? null;

        if (!$modul || $index === null || !$field) return;

        $modulComponent = ModulComponent::where('modul', $modul)->first();
        if (!$modulComponent) return;

        $componentList = $this->parseComponentData($modulComponent->component);
        $relativeIndex = $index - 1;

        if (!isset($componentList[$relativeIndex])) return;

        // Update the specific field
        $componentList[$relativeIndex][$field] = $value;

        // If component name changed, merge with PartComponent data
        if ($field === 'name') {
            $partComponent = $this->getPartComponentByName($value);
            if ($partComponent) {
                $componentData = $this->extractPartComponentData($partComponent);
                if ($componentData) {
                    $componentList[$relativeIndex] = array_merge(
                        $componentList[$relativeIndex],
                        $componentData
                    );
                }
            }
        }

        $modulComponent->component = json_encode($componentList);
        $modulComponent->save();
        $this->loadGroupedComponents();
    }

    protected function getPartComponentByName($name)
    {
        return PartComponent::where('part_component', 'like', '%"name":"' . $name . '"%')->first();
    }

    protected function extractPartComponentData($partComponent)
    {
        if (empty($partComponent->part_component)) {
            return null;
        }

        $decoded = json_decode($partComponent->part_component, true);
        if (json_last_error() !== JSON_ERROR_NONE || !isset($decoded[0]['data'])) {
            return null;
        }

        return $decoded[0]['data'];
    }

    protected function transformPartComponent($partComponent)
    {
        return $this->extractPartComponentData($partComponent) ?? [];
    }

    public function handleModulUpdate($modul)
    {
        $this->modul = is_array($modul) ? $modul : [$modul];
        $this->loadGroupedComponents();
        $this->loadDropdownData();
    }

    public function updatedModul()
    {
        $this->loadGroupedComponents();
    }

    public function loadDropdownData()
    {
        $parts = PartComponent::all();
        $types = [];
        $options = [];

        foreach ($parts as $part) {
            // Decode the JSON string from part_component
            $decoded = json_decode($part->part_component, true);

            // Skip if JSON is invalid
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

                // Skip if no name is set
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

        if (!empty($this->modul)) {
            $this->loadComponentsFromModulComponent($this->modul);
            return;
        }

        $decodedModuls = $this->getDecodedModuls();

        if ($this->recordId) {
            $this->loadComponentsFromModulComponent($decodedModuls);
        }

        $this->loadMissingModuls($decodedModuls);
        $this->ensureAllModulsPresent($decodedModuls);
    }

    protected function loadComponentsFromModulComponent($modulName)
    {
        $modulData = ModulComponent::where('modul', $modulName)->first();

        if ($modulData) {
            $componentList = $this->parseComponentData($modulData->component);
            $processedComponents = $this->processComponents($componentList ?? []);

            $this->groupedComponents['array'][] = [
                'modul' => ['nama_modul' => $modulName],
                'component' => $processedComponents,
                'isFilled' => false
            ];
        }
    }

    // Maintain all the original reference methods unchanged
    protected function getDecodedModuls()
    {
        return array_map(function ($item) {
            return is_string($item) ? json_decode('"' . $item . '"') : $item;
        }, $this->modul);
    }

    protected function loadMissingModuls($decodedModuls)
    {
        $existingModuls = array_column(array_column($this->groupedComponents['array'], 'modul'), 'nama_modul');
        $missingModuls = array_diff($decodedModuls, $existingModuls);

        if (empty($missingModuls)) {
            return;
        }

        foreach ($missingModuls as $modulName) {
            $this->loadComponentsFromModulComponent($modulName);
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

    protected function parseComponentData($componentData)
    {
        if (is_string($componentData)) {
            $data = json_decode($componentData, true) ?? [];
            return is_array($data) ? $data : [];
        }

        return is_array($componentData) ? $componentData : [];
    }

    public function updatedGroupedComponents($value, $key)
    {
        $parts = explode('.', $key);
        if (count($parts) !== 3) return;

        [$modul, $index, $field] = $parts;

        $modulComponent = ModulComponent::where('modul', $modul)->first();
        if (!$modulComponent) return;

        $componentList = $this->parseComponentData($modulComponent->component);

        if (!isset($componentList[$index])) return;

        $componentList[$index][$field] = $value;

        if ($field === 'name') {
            $partComponent = $this->getPartComponentByName($value);
            if ($partComponent) {
                $componentData = $this->extractPartComponentData($partComponent);
                if ($componentData) {
                    $componentList[$index] = array_merge(
                        $componentList[$index],
                        $componentData
                    );
                }
            }
        }

        $modulComponent->component = json_encode($componentList);
        $modulComponent->save();
        $this->loadGroupedComponents();
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

    public function loadPartComponentData()
    {
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
            'modul' => 'string',
            'reference_modul' => 'nullable|string',
            'components' => 'required|array',
        ]);

        try {
            $mainModul = $validated['modul'];
            $referenceModul = $validated['reference_modul'];
            $componentsData = json_encode($validated['components']);


            $modulComponent = ModulComponent::firstOrNew(['modul' => $mainModul]);
            $modulComponent->component = ($componentsData);
            $modulComponent->reference_modul = $referenceModul;
            $modulComponent->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Data berhasil disimpan',
                'modul' => $mainModul
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
            'modul' => 'string',
            'reference_modul' => 'nullable|string',
            'components' => 'required|array',
            'columns' => 'required|array',
            'recordId' => 'required|integer'
        ]);

        try {
            $mainModul = $validated['modul'];
            $referenceModul = $validated['reference_modul'];
            $componentsData = json_encode($validated['components']);
            $recordId = $validated['recordId'];

            $modulComponent = ModulComponent::findOrFail($recordId);

            $modulComponent->component = $componentsData;
            $modulComponent->reference_modul = $referenceModul;
            $modulComponent->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Data berhasil diupdate',
                'modul' => $modulComponent->modul
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengupdate data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function render()
    {
        return view('livewire.komponen-modul');
    }
}
