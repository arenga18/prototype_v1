<?php

namespace App\Http\Livewire;

use Livewire\Component;

class KomponenTable extends Component
{
    public $komponen = [];

    public function mount($komponen = [])
    {
        $this->komponen = $komponen;
    }

    public function updatedKomponen()
    {
        // Opsional: validasi atau hitung subtotal saat cell berubah
    }

    public function render()
    {
        return view('livewire.komponen-table');
    }
}

