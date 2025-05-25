<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ModulComponent;

class KomponenTable extends Component
{
    public $moduls = [];
    public $groupedComponents = [];

    public $columns;

    public function __construct()
    {
        $this->columns = config('breakdown_fields.breakdown_col');
    }

    public function mount($moduls)
    {
        $this->moduls = $moduls ?? [];
        $this->loadGroupedComponents();
    }

    public function loadGroupedComponents()
    {
        $decodedModuls = array_map(function ($item) {
            return json_decode('"' . $item . '"');
        }, $this->moduls);

        $components = ModulComponent::whereIn('modul', $decodedModuls)->get();

        $this->groupedComponents = $components->mapWithKeys(function ($modulComponent) {
            $modul = $modulComponent->modul;
            $componentList = is_string($modulComponent->component)
                ? json_decode($modulComponent->component, true)
                : $modulComponent->component;


            return [$modul => $componentList];
        })->toArray();
    }

    public function updatedGroupedComponents($value, $key)
    {
        [$modul, $index, $field] = explode('.', $key);

        $modulComponent = ModulComponent::where('modul', $modul)->first();

        if ($modulComponent) {
            $componentList = is_string($modulComponent->component)
                ? json_decode($modulComponent->component, true)
                : $modulComponent->component;
            $componentList[$index][$field] = $value;
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
