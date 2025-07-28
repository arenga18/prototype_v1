<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\PartComponent;
use App\Models\RemovablePart;
use Illuminate\Http\Request;

class RemovablePartLivewire extends Component
{
    public $part = [];
    public $partList = [];
    public $partData = [];
    public $partReference = [];
    public $partComponentsData = [];
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
        'partUpdated' => 'handlePartUpdate',
        'refresh' => '$refresh'
    ];

    public function mount($part = [], $recordId = null)
    {
        $this->columns = config('breakdown_fields.breakdown_col') ?? [];
        $this->fieldMapping = config('breakdown_fields.field_mapping') ?? [];
        $this->dataValidationCol = config("breakdown_fields.data_validation_col");
        $this->dataValMap = config("breakdown_fields.data_val_map");
        $this->recordId = $recordId;

        if ($recordId) {
            $removablePart = RemovablePart::find($recordId);
            if ($removablePart) {
                $this->part = $removablePart->part;
                $this->partData = [$removablePart->part => $removablePart->component];
            } else {
                $this->partData = [];
            }
        }

        $this->loadDropdownData();
        $this->loadPartComponentData();
        $this->loadDefinedNames();
    }

    public function getPartData(Request $request)
    {
        $part = $request->query('part');
        $partData = RemovablePart::where('part', $part)->first();

        if ($partData) {
            return response()->json([
                'success' => true,
                'components' => $partData->component
            ]);
        }

        return response()->json([
            'success' => false,
            'components' => null
        ]);
    }

    public function updateGroupedComponent($payload)
    {
        $part = $payload['part'] ?? null;
        $index = $payload['index'] ?? null;
        $field = $payload['field'] ?? null;
        $value = $payload['value'] ?? null;

        if (!$part || $index === null || !$field) return;

        $removablePart = RemovablePart::where('part', $part)->first();
        if (!$removablePart) return;

        $componentList = $this->parseComponentData($removablePart->component);
        $relativeIndex = $index - 1;

        if (!isset($componentList[$relativeIndex])) return;

        $componentList[$relativeIndex][$field] = $value;

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

        $removablePart->component = json_encode($componentList);
        $removablePart->save();
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

    public function handlePartUpdate($part)
    {
        $this->part = is_array($part) ? $part : [$part];
        $this->loadGroupedComponents();
        $this->loadDropdownData();
    }

    public function updatedPart()
    {
        $this->loadGroupedComponents();
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

            foreach ($decoded as $item) {
                if (!isset($item['data'])) {
                    continue;
                }

                $componentData = $item['data'];

                if (!isset($componentData['name'])) {
                    continue;
                }

                // if (isset($componentData['code'])) {
                //     $types[$componentData['code']] = [
                //         'value' => $componentData['code'],
                //         'label' => $componentData['code']
                //     ];
                // }

                // $options[] = [
                //     'value' => $componentData['name'],
                //     'label' => $componentData['name'],
                //     'data' => $componentData
                // ];
            }
        }

        $this->componentTypes = array_values($types);
        $this->componentOptions = $options;
    }

    public function loadGroupedComponents()
    {
        $this->groupedComponents = [];

        if (!empty($this->part)) {
            $partData = RemovablePart::where('part', $this->part)->first();
            if ($partData) {
                $componentData = $this->parseComponentData($partData->component);
                if ($componentData) {
                    $this->groupedComponents[$this->part] = $componentData;
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

        [$part, $index, $field] = $parts;

        $removablePart = RemovablePart::where('part', $part)->first();
        if (!$removablePart) return;

        $componentList = $this->parseComponentData($removablePart->component);

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

        $removablePart->component = json_encode($componentList);
        $removablePart->save();
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

                    foreach ($decoded as $item) {
                        if (isset($item['data']) && is_array($item['data'])) {
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
        $definedNames = $allPartComponents[0]["defined_names"] ?? [];
        $this->definedNames = $definedNames;
    }

    public function save(Request $request)
    {
        $validated = $request->validate([
            'part' => 'required|string',
            'component' => 'required|array',
        ]);

        try {
            $part = $validated['part'];
            $componentsData = $validated['component'];

            $componentsToSave = [];
            foreach ($componentsData as $component) {
                if (isset($component['data'])) {
                    $componentsToSave[] = $component['data'];
                }
            }

            $removablePart = RemovablePart::firstOrNew(['part' => $part]);
            $removablePart->part = $part;
            $removablePart->component = json_encode($componentsToSave);
            $removablePart->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Data berhasil disimpan',
                'part' => $part,
                'component_count' => count($componentsToSave)
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
            'part' => 'required|string',
            'components' => 'required|array',
            'recordId' => 'required|integer'
        ]);

        try {
            $part = $validated['part'];
            $componentsData = $validated['components'];
            $recordId = $validated['recordId'];

            $removablePart = RemovablePart::findOrFail($recordId);
            $componentsToSave = [];

            foreach ($componentsData as $component) {
                if (isset($component['data'])) {
                    $componentsToSave[] = $component['data'];
                }
            }

            $removablePart->part = $part;
            $removablePart->component = json_encode($componentsToSave);
            $removablePart->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Data berhasil diupdate',
                'part' => $part
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
        return view('livewire.removable-parts-livewire');
    }
}
