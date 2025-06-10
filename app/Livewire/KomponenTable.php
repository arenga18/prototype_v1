<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ModulComponent;
use App\Models\Project;
use App\Models\PartComponent;
use Illuminate\Http\Request;

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
    protected $listeners = ['typeChanged'];

    public function typeChanged($type)
    {
        $this->componentOptions = PartComponent::where('kode', $type)
            ->pluck('name')
            ->toArray();

        $this->emit('updateComponentOptions', $this->componentOptions);
    }

    public function mount($moduls,  $recordId = null)
    {
        $this->columns = config('breakdown_fields.breakdown_col');
        $this->fieldMapping = config('breakdown_fields.fields_mapping');
        $this->dataValidationCol = config("breakdown_fields.data_validation_col");
        $this->dataValMap = config("breakdown_fields.data_val_map");
        $this->recordId = $recordId;
        $this->moduls = $moduls ?? [];
        $this->loadGroupedComponents();
        $this->loadPartComponent();
        $this->loadDropdownData();
    }

    public function loadDropdownData()
    {
        // Load data untuk dropdown type
        $this->componentTypes = PartComponent::select('code')
            ->distinct()
            ->get()
            ->map(function ($item) {
                return [
                    'value' => $item->code,
                    'label' => $item->code,
                ];
            })
            ->toArray();

        // Load initial component options dengan semua kolom yang diperlukan
        $this->componentOptions = PartComponent::all()
            ->map(function ($item) {
                return [
                    'value' => $item->name,
                    'label' => $item->name,
                    'data' => $item->toArray() // Sertakan semua data
                ];
            })
            ->toArray();
        // dd($this->componentOptions);
    }

    public function loadGroupedComponents()
    {
        $this->groupedComponents = [
            'array' => [] // Struktur utama dengan array
        ];

        $decodedModuls = array_map(function ($item) {
            return is_string($item) ? json_decode('"' . $item . '"') : $item;
        }, $this->moduls);

        // First try to get data from Project->modul_breakdown
        if ($this->recordId) {
            $project = Project::find($this->recordId);

            if ($project && !empty($project->modul_breakdown)) {
                $modulBreakdown = json_decode($project->modul_breakdown, true);

                if (json_last_error() === JSON_ERROR_NONE && !empty($modulBreakdown)) {
                    foreach ($modulBreakdown as $item) {
                        if (isset($item['modul']['nama_modul'])) {
                            $modulName = $item['modul']['nama_modul'];
                            // Skip empty nama_modul arrays
                            if (is_array($modulName) && empty($modulName)) {
                                continue;
                            }

                            // Membentuk struktur object yang diminta
                            $this->groupedComponents['array'][] = [
                                'modul' => $item['modul'],
                                'component' => $item['components'] ?? [],
                                'isFilled' => true,
                            ];
                        }
                    }
                }
            }
        }

        $missingModuls = array_diff($decodedModuls, array_column(array_column($this->groupedComponents['array'], 'modul'), 'nama_modul'));

        if (!empty($missingModuls)) {
            $components = ModulComponent::whereIn('modul', $missingModuls)->get();

            foreach ($components as $modulComponent) {
                $modul = $modulComponent->modul;
                $componentList = $this->parseComponentData($modulComponent->component);

                // Get basic part component data for each component
                if (is_array($componentList)) {
                    $componentList = array_map(function ($comp) {
                        if (isset($comp['component'])) {
                            $partComponent = PartComponent::where('name', $comp['component'])->first();
                            if ($partComponent) {
                                return array_merge($comp, $partComponent->toArray());
                            }
                        }
                        return $comp;
                    }, $componentList);
                }

                // Menambahkan modul yang belum ada ke dalam struktur
                $this->groupedComponents['array'][] = [
                    'modul' => ['nama_modul' => $modul],
                    'component' => $componentList ?? [],
                    'isFilled' => false
                ];
            }
        }

        // Memastikan semua modul ada dalam struktur
        foreach ($decodedModuls as $modul) {
            $found = false;
            foreach ($this->groupedComponents['array'] as $item) {
                if ($item['modul']['nama_modul'] === $modul) {
                    $found = true;
                    break;
                }
            }

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
            $decoded = json_decode($componentData, true);
            return json_last_error() === JSON_ERROR_NONE ? $decoded : null;
        }

        if (is_array($componentData)) {
            return $componentData;
        }

        return null;
    }

    public function updatedGroupedComponents($value, $key)
    {
        [$modul, $index, $field] = explode('.', $key);

        $modulComponent = ModulComponent::with('partComponents')
            ->where('modul', $modul)
            ->first();

        if ($modulComponent) {
            $componentList = $this->parseComponentData($modulComponent->component) ?? [];

            // Update the field value
            $componentList[$index][$field] = $value;

            // Jika component diubah, cari data partComponent yang sesuai
            if ($field === 'component') {
                $partComponent = $modulComponent->partComponents
                    ->firstWhere('name', $value);

                if ($partComponent) {
                    // Gabungkan data partComponent ke component
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
    }

    public function loadPartComponent()
    {
        $uniqueComponents = [];
        $seen = [];

        $decodedModuls = array_map(function ($item) {
            return is_string($item) ? json_decode('"' . $item . '"') : $item;
        }, $this->moduls);

        $components = ModulComponent::whereIn('modul', $decodedModuls)->get();

        foreach ($components as $modulComponent) {
            $componentNames = $this->parseComponentData($modulComponent->component);

            if (is_array($componentNames)) {
                foreach ($componentNames as $comp) {
                    if (isset($comp['component'])) {
                        $componentName = $comp['component'];

                        $part = PartComponent::where('name', $componentName)->first();

                        if ($part) {
                            // Gunakan kombinasi unik, misalnya berdasarkan name + cat
                            $key = $part->name . '|' . $part->cat;

                            if (!isset($seen[$key])) {
                                $uniqueComponents[] = $part->toArray();
                                $seen[$key] = true;
                            }
                        }
                    }
                }
            }
        }

        // Langsung assign ke partComponentsData tanpa kategori
        $this->partComponentsData = $uniqueComponents;
    }




    public function save(Request $request)
    {
        $validated = $request->validate([
            'breakdown_modul' => 'required|array',
            'columns' => 'required|array'
        ]);

        try {
            // Simpan data breakdown_modul dan columns ke database
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
            // Ambil data dari request
            $breakdownModul = $validated['modul_breakdown'];
            $columns = $validated['columns'];
            $recordId = $validated['recordId'];

            // Temukan dan update record
            $project = Project::findOrFail($recordId);

            // Update data utama
            $project->modul_breakdown = json_encode($breakdownModul);
            // $project->columns = json_encode($columns)

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
    public function render()
    {
        return view('livewire.komponen-table');
    }
}
