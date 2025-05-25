<?php

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;

use App\Models\DescriptionUnit;
use App\Models\BoxCarcaseShape;
use App\Models\Finishing;
use App\Models\LayerPosition;
use App\Models\BoxCarcaseContent;
use App\Models\ClosingSystem;
use App\Models\NumberOfClosure;
use App\Models\TypeOfClosure;
use App\Models\Handle;
use App\Models\Accessories;
use App\Models\Lamp;
use App\Models\Plinth;

return [
    'textInputs' => [
        DatePicker::make('input_date')->label('Tanggal Input')->required(),
        TextInput::make('nip')->label('NIP')->required(),
        TextInput::make('height')->label('Tinggi')->required(),
        TextInput::make('project_name')->label('Nama Proyek')->required(),
        TextInput::make('product_name')->label('Nama Produk')->required(),
    ],

    'spesifikasiFields' => [
        'product_spesification' => 'Spesifikasi Produk',
        'material_thickness_spesification' => 'Ketebalan Material',
        'coating_spesification' => 'Coating',
        'alu_frame_spesification' => 'Aluminium Frame',
        'hinges_spesification' => 'Engsel (Hinges)',
        'rail_spesification' => 'Rel (Rail)',
        'glass_spesification' => 'Kaca',
        'profile_spesification' => 'Profil',
        'size_distance_spesification' => 'Jarak Ukuran',
    ],

    'selectFields' => [
        'description_unit' => ['label' => 'Deskripsi Unit', 'model' => DescriptionUnit::class],
        'box_carcase_shape' => ['label' => 'Bentuk Box/Carcase', 'model' => BoxCarcaseShape::class],
        'finishing' => ['label' => 'Finishing', 'model' => Finishing::class],
        'layer_position' => ['label' => 'Posisi Lapisan', 'model' => LayerPosition::class],
        'box_carcase_content' => ['label' => 'Isi Box/Carcase', 'model' => BoxCarcaseContent::class],
        'closing_system' => ['label' => 'Sistem Tutup', 'model' => ClosingSystem::class],
        'number_of_closures' => ['label' => 'Jumlah Tutup', 'model' => NumberOfClosure::class],
        'type_of_closure' => ['label' => 'Jenis Tutup', 'model' => TypeOfClosure::class],
        'handle' => ['label' => 'Handle', 'model' => Handle::class],
        'acc' => ['label' => 'Accessories', 'model' => Accessories::class],
        'lamp' => ['label' => 'Lampu', 'model' => Lamp::class],
        'plinth' => ['label' => 'Plinth', 'model' => Plinth::class],
    ],
];
