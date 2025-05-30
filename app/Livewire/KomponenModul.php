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
        $this->recordId = $recordId;

        if ($recordId) {
            // Ambil modul berdasarkan ID dari URL
            $modulComponent = ModulComponent::find($recordId);
            if ($modulComponent) {
                $this->modulList = [$modulComponent->modul];
                $this->modulData = [$modulComponent->modul => $modulComponent->component];
            } else {
                $this->modulList = [];
            }
        } else {
            // Jika tidak ada recordId, tampilkan semua
            $this->modulList = Modul::all()->pluck('code_cabinet')->toArray();
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
        } else {
            return response()->json([
                'success' => false,
                'components' => null
            ]);
        }
    }


    public function updateGroupedComponent($payload)
    {
        $modul = $payload['modul'];
        $index = $payload['index'];
        $field = $payload['field'];
        $value = $payload['value'];

        if (!$modul || $index === null || !$field) return;

        $modulComponent = ModulComponent::where('modul', $modul)->first();
        if (!$modulComponent) return;

        $componentList = $this->parseComponentData($modulComponent->component);

        // Cari index relatif terhadap komponen (bukan row di spreadsheet)
        $relativeIndex = $index - 1; // Adjust jika baris pertama modul = 0
        if (!isset($componentList[$relativeIndex])) return;

        $componentList[$relativeIndex][$field] = $value;

        if ($field === 'component') {
            $partComponent = PartComponent::where('name', $value)->first();
            if ($partComponent) {
                $componentList[$relativeIndex] = array_merge(
                    $componentList[$relativeIndex],
                    $partComponent->toArray()
                );
            }
        }

        $modulComponent->component = json_encode($componentList);
        $modulComponent->save();

        $this->loadGroupedComponents();
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
        $this->componentOptions = PartComponent::all()
            ->map(function ($item) {
                return [
                    'value' => $item->name,
                    'label' => $item->name,
                    'data' => $item->toArray()
                ];
            })
            ->toArray();
    }

    public function loadGroupedComponents()
    {
        $this->groupedComponents = [];

        echo "Modul yang dipilih: " . implode(', ', $this->modul) . "\n";

        if ($this->modul) {
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
            return json_decode($componentData, true) ?? [];
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

        // Jika component diubah, update data terkait
        if ($field === 'component') {
            $partComponent = PartComponent::where('name', $value)->first();
            if ($partComponent) {
                $componentList[$index] = array_merge(
                    $componentList[$index],
                    $partComponent->toArray()
                );
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
            'data' => 'required|array',
            'columns' => 'required|array'
        ]);

        try {
            $modul = $validated['modul'];
            $referenceModul = $validated['reference_modul'];
            $spreadsheetData = $validated['data'];
            $columns = $validated['columns'];

            // Cari atau buat record modul
            $modulComponent = ModulComponent::firstOrNew(['modul' => $modul]);

            // Proses data spreadsheet
            $components = [];
            $currentModul = '';

            foreach ($spreadsheetData as $row) {
                // Skip baris kosong
                if (empty(array_filter($row))) continue;

                // Cek jika baris berisi nama modul
                $modulIndex = array_search('nama_modul', $columns);
                if ($modulIndex !== false && !empty($row[$modulIndex])) {
                    $currentModul = $row[$modulIndex];
                    continue;
                }

                // Skip jika tidak ada modul yang aktif
                if (empty($currentModul)) continue;

                // Proses baris komponen
                $component = [];
                foreach ($columns as $index => $column) {
                    if (isset($row[$index]) && $row[$index] !== '') {
                        $component[$column] = $row[$index];
                    }
                }

                if (!empty($component)) {
                    $components[] = $component;
                }
            }

            // Simpan ke database
            $modulComponent->component = json_encode($components);
            $modulComponent->reference_modul = $referenceModul;
            $modulComponent->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Data berhasil disimpan',
                'modul' => $modul
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
            'data' => 'required|array',
            'columns' => 'required|array',
            'recordId' => 'required|integer'
        ]);

        try {
            $modul = $validated['modul'];
            $referenceModul = $validated['reference_modul'];
            $spreadsheetData = $validated['data'];
            $columns = $validated['columns'];
            $recordId = $validated['recordId'];

            // Cari record modul yang akan diupdate
            $modulComponent = ModulComponent::findOrFail($recordId);

            // Proses data spreadsheet
            $components = [];
            $currentModul = '';

            foreach ($spreadsheetData as $row) {
                // Skip baris kosong
                if (empty(array_filter($row))) continue;

                // Cek jika baris berisi nama modul
                $modulIndex = array_search('nama_modul', $columns);
                if ($modulIndex !== false && !empty($row[$modulIndex])) {
                    $currentModul = $row[$modulIndex];
                    continue;
                }

                // Skip jika tidak ada modul yang aktif
                if (empty($currentModul)) continue;

                // Proses baris komponen
                $component = [];
                foreach ($columns as $index => $column) {
                    if (isset($row[$index])) {
                        $component[$column] = $row[$index];
                    }
                }

                if (!empty($component)) {
                    $components[] = $component;
                }
            }

            // Update data
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
