<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ModulComponent;
use App\Models\PartComponent;

class KomponenTable extends Component
{
    public $moduls = [];
    public $groupedComponents = [];
    public $columns;
    public $componentTypes = []; // Untuk dropdown type
    public $componentOptions = [];
    protected $listeners = ['typeChanged'];

    public function typeChanged($type)
    {
        $this->componentOptions = PartComponent::where('kode', $type)
            ->pluck('name')
            ->toArray();

        $this->emit('updateComponentOptions', $this->componentOptions);
    }

    public function __construct()
    {
        $this->columns = config('breakdown_fields.breakdown_col');
    }

    public function mount($moduls)
    {
        $this->moduls = $moduls ?? [];
        $this->loadGroupedComponents();
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
        $decodedModuls = array_map(function ($item) {
            return is_string($item) ? json_decode('"' . $item . '"') : $item;
        }, $this->moduls);

        // Ambil semua komponen termasuk relasi partComponents
        $components = ModulComponent::whereIn('modul', $decodedModuls)->get();

        // Kumpulkan semua name component yang unik
        $componentNames = [];
        foreach ($components as $modulComponent) {
            $componentList = $this->parseComponentData($modulComponent->component);
            if (is_array($componentList)) {
                foreach ($componentList as $comp) {
                    if (isset($comp['component']) && is_string($comp['component'])) {
                        $componentNames[] = $comp['component'];
                    }
                }
            }
        }

        // Ambil semua partComponents yang relevan sekaligus
        $partComponents = PartComponent::whereIn('name', array_unique($componentNames))
            ->get()
            ->keyBy('name');

        // Proses pengelompokan komponen
        $dbComponents = $components->mapWithKeys(function ($modulComponent) use ($partComponents) {
            $modul = $modulComponent->modul;
            $componentList = $this->parseComponentData($modulComponent->component);

            if (is_array($componentList)) {
                $componentList = array_map(function ($comp) use ($partComponents) {
                    if (isset($comp['component']) && $partComponents->has($comp['component'])) {
                        return array_merge($comp, $partComponents[$comp['component']]->toArray());
                    }
                    return $comp;
                }, $componentList);
            }

            return [$modul => $componentList ?? []];
        })->toArray();

        // Pastikan semua modul yang di-decode muncul
        $this->groupedComponents = [];
        foreach ($decodedModuls as $modul) {
            $this->groupedComponents[$modul] = $dbComponents[$modul] ?? [];
        }

        // dd($this->groupedComponents);
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

    public function render()
    {
        return view('livewire.komponen-table');
    }
}
