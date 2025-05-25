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
        // Decode modul names if they're encoded
        $decodedModuls = array_map(function ($item) {
            return is_string($item) ? json_decode('"' . $item . '"') : $item;
        }, $this->moduls);

        // Get components for the selected moduls
        $components = ModulComponent::whereIn('modul', $decodedModuls)->get();


        // Process and group components
        $this->groupedComponents = $components->mapWithKeys(function ($modulComponent) {
            $modul = $modulComponent->modul;
            $componentList = $this->parseComponentData($modulComponent->component);

            return [$modul => $componentList ?? []];
        })->filter()->toArray();
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

        $modulComponent = ModulComponent::where('modul', $modul)->first();

        if ($modulComponent) {
            $componentList = $this->parseComponentData($modulComponent->component) ?? [];
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
