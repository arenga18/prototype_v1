<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\PartComponent;
use App\Models\ModulComponent;
use App\Models\Modul;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class KomponenModul extends Component
{
    public $modul = [];
    public $modulList = [];
    public $modulData = [];
    public $modulReference = [];
    public $groupedComponents = [];
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
        $this->recordId = $recordId;

        if ($recordId) {
            $modulComponent = ModulComponent::find($recordId);
            if ($modulComponent) {
                $this->modulList = [$modulComponent->modul];
                $this->modulData = [$modulComponent->modul => $modulComponent->component];
            } else {
                $this->modulList = [];
            }
        } else {
            $usedModuls = ModulComponent::pluck('modul')->toArray();
            $this->modulList = Modul::whereNotIn('code_cabinet', $usedModuls)
                ->pluck('code_cabinet')
                ->toArray();
        }

        $this->modulReference = ModulComponent::all()->pluck('modul')->toArray();
        $this->loadDropdownData();
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
        $relativeIndex = $index - 1; // Adjust for zero-based index

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
        $this->groupedComponents = [];

        if (!empty($this->modul)) {
            $modulData = ModulComponent::where('modul', $this->modul)->first();
            if ($modulData) {
                $componentData = $this->parseComponentData($modulData->component);
                if ($componentData) {
                    $this->groupedComponents[$this->modul] = $componentData;
                }
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

    public function save(Request $request)
    {
        $validated = $request->validate([
            'modul' => 'required|string',
            'reference_modul' => 'nullable|string',
            'components' => 'required|array',
            'columns' => 'required|array'
        ]);

        try {
            $mainModul = $validated['modul'];
            $referenceModul = $validated['reference_modul'];
            $componentsData = $validated['components'];
            $columns = $validated['columns'];

            $modulComponents = [];
            foreach ($componentsData as $component) {
                $modulName = $component['modul'];
                if (!isset($modulComponents[$modulName])) {
                    $modulComponents[$modulName] = [];
                }
                $modulComponents[$modulName][] = $component['data'];
            }

            foreach ($modulComponents as $modulName => $components) {
                $modulComponent = ModulComponent::firstOrNew(['modul' => $modulName]);
                $modulComponent->component = json_encode($components);
                $modulComponent->reference_modul = ($modulName === $mainModul) ? $referenceModul : null;
                $modulComponent->save();
            }

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
            'modul' => 'required|string',
            'reference_modul' => 'nullable|string',
            'components' => 'required|array',
            'columns' => 'required|array',
            'recordId' => 'required|integer'
        ]);

        try {
            $mainModul = $validated['modul'];
            $referenceModul = $validated['reference_modul'];
            $componentsData = $validated['components'];
            $columns = $validated['columns'];
            $recordId = $validated['recordId'];

            $modulComponent = ModulComponent::findOrFail($recordId);
            $components = [];

            foreach ($componentsData as $component) {
                if ($component['modul'] === $mainModul) {
                    $components[] = $component['data'];
                }
            }

            $modulComponent->component = json_encode($components);
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
