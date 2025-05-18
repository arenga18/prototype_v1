<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ModulComponent;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

class KomponenTable extends Component
{
    public $komponen = [];

    protected $rules = [
        'komponen.*.component' => 'nullable|string',
        'komponen.*.p_value' => 'nullable|numeric',
        'komponen.*.l_value' => 'nullable|numeric',
        'komponen.*.t_value' => 'nullable|numeric',
        'komponen.*.qty' => 'nullable|numeric',
    ];

    public $modul = [
    'p_value' => null,
    'l_value' => null,
    't_value' => null,
    'qty'     => null,
];

    public function mount()
{
    $modulName = "B'PanelPm║V→◄";

    $modulComponent = ModulComponent::where('modul', $modulName)->first();

    $data = [];

    // Pastikan baris modul ditambahkan dulu
    $data[] = [
        'component' => $modulName,
        'p_value'   => null,
        'l_value'   => null,
        't_value'   => null,
        'qty'       => null,
    ];

    // Jika ada komponen dari DB, tambahkan
    if ($modulComponent && is_array($modulComponent->component)) {
        $komponenTambahan = collect($modulComponent->component)->map(function ($item) {
            return [
                'component' => $item['component'] ?? '',
                'p_value'   => $item['p_value'] ?? null,
                'l_value'   => $item['l_value'] ?? null,
                't_value'   => $item['t_value'] ?? null,
                'qty'       => $item['qty'] ?? 0,
            ];
        })->toArray();

        $data = array_merge($data, $komponenTambahan);
    }

    $this->komponen = $data;
}


    public function updatedKomponen($value, $key)
{
    // Jika perubahan ada di baris pertama (index 0)
    if (str_starts_with($key, '0.')) {
        $this->hitungKomponenBerdasarkanModul();
    }
}

public function hitungKomponenBerdasarkanModul()
{
    $modul = $this->komponen[0];

    $p = (float) $modul['p_value'];
    $l = (float) $modul['l_value'];
    $t = (float) $modul['t_value'];
    $qty = (float) $modul['qty'];

    foreach ($this->komponen as $i => $item) {
        if ($i === 0) continue;

        switch ($item['component']) {
            case 'Ganjelan top table':
                $this->komponen[$i]['p_value'] = $t;
                $this->komponen[$i]['l_value'] = $l;
                $this->komponen[$i]['t_value'] = $l - $t;
                $this->komponen[$i]['qty'] = $qty;
                break;

            case 'Kaki Meja':
                $this->komponen[$i]['p_value'] = $p / 2;
                $this->komponen[$i]['l_value'] = $l;
                $this->komponen[$i]['t_value'] = $t;
                $this->komponen[$i]['qty'] = $qty * 2;
                break;

            // Tambahkan komponen lainnya di sini

            default:
                // Tidak diubah
                break;
        }
    }
}

    public function render()
    {
        return view('livewire.komponen-table');
    }
}
