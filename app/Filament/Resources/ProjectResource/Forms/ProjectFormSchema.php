<?php

namespace App\Filament\Resources\ProjectResource\Forms;

use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Livewire;
use Filament\Forms\Components\CheckboxList;
use Awcodes\TableRepeater\Components\TableRepeater;
use Awcodes\TableRepeater\Header;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;

use Illuminate\Support\Facades\Log;

use App\Models\Material;
use App\Models\Modul;
use App\Filament\Resources\ProjectResource\Forms\SelectComponents;

class ProjectFormSchema
{
    public static function getSchema(): array
    {
        $selectFields = config('project_fields.selectFields', []);
        $textInputs = config('project_fields.textInputs', []);
        $defaultSpecifications  = config('project_default_value', []);

        return [
            Wizard::make([
                // Step 1: Informasi Proyek dan Referensi Modul
                Wizard\Step::make('Informasi Proyek')
                    ->schema([
                        Section::make('Informasi Dasar')
                            ->schema([
                                DatePicker::make('date')->label('Tanggal')->required(),
                                TextInput::make('recap_number')->label('No Rekap')->required(),
                                TextInput::make('no_contract')->label('No Kontrak')->required(),
                                TextInput::make('nip')
                                    ->label('NIP')
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function (callable $set, $state) {

                                        $moduls = Modul::where('nip', $state)->get();

                                        if ($moduls->isNotEmpty()) {

                                            $modulCodes = $moduls->pluck('code_cabinet')->toArray();
                                            $set('modul_reference', $modulCodes);

                                            $firstModul = $moduls->first();
                                            $set('product_name', $firstModul->product_name);
                                            $set('project_name', $firstModul->project_name);
                                        } else {
                                            $set('modul_reference', []);
                                            $set('product_name', null);
                                            $set('project_name', null);
                                        }
                                    }),
                                TextInput::make('product_name')->label('Nama Produk')
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function (callable $set, $state) use ($defaultSpecifications) {
                                        $productName = strtolower(trim($state ?? 'pantry'));
                                        $set('product_name', $productName);

                                        $defaults = $defaultSpecifications[$productName] ?? $defaultSpecifications['pantry'];

                                        $set('product_spesification', $defaults['product_spesification'] ?? []);
                                        $set('material_thickness_spesification', $defaults['material_thickness_spesification'] ?? []);
                                        $set('coating_standard', $defaults['coating_standard'] ?? []);
                                        $set('coating_spesification', $defaults['coating_spesification'] ?? []);
                                        $set('komp_anodize_spesification', $defaults['komp_anodize_spesification'] ?? []);
                                        $set('alu_frame_spesification', $defaults['alu_frame_spesification'] ?? []);
                                        $set('hinges_spesification', $defaults['hinges_spesification'] ?? []);
                                        $set('rail_spesification', $defaults['rail_spesification'] ?? []);
                                        $set('door_mechanism', $defaults['door_mechanism'] ?? []);
                                        $set('glass_spesification', $defaults['glass_spesification'] ?? []);
                                        $set('profile_spesification', $defaults['profile_spesification'] ?? []);
                                        $set('size_distance_spesification', $defaults['size_distance_spesification'] ?? []);
                                    }),
                                TextInput::make('project_name')
                                    ->label('Nama Proyek')
                                    ->required(),
                                Select::make('estimator')
                                    ->label("Estimator")
                                    ->options(
                                        [
                                            'fahrudin' => "Fahrudin",
                                            'guruh' => "Guruh",
                                            'suminto' => "Suminto",
                                        ]
                                    )
                                    ->searchable()
                                    ->extraAttributes([
                                        'style' => 'border: none !important; border-radius: 0 !important;',
                                    ]),
                                Select::make('recap_coordinator')
                                    ->label("Kordinator Rekap")
                                    ->options(
                                        [
                                            'reza' => "Reza Pahlevi",
                                            'ade' => "Ade Irawan",
                                            'randy' => "Randy MF",
                                            'nael' => "Nael Arya",
                                        ]
                                    )
                                    ->searchable()
                                    ->extraAttributes([
                                        'style' => 'border: none !important; border-radius: 0 !important;',
                                    ]),
                                CheckboxList::make('project_status')
                                    ->label('Status Proyek')
                                    ->options([
                                        'Pendingan' => 'Pendingan',
                                        'Anti Rayap' => 'Anti Rayap',
                                    ])
                                    ->required(),
                            ])->columns(2),

                        Section::make('Spesifikasi')
                            ->schema([
                                TableRepeater::make('product_spesification')
                                    ->streamlined()
                                    ->label('Spesifikasi Produk')
                                    ->headers([
                                        Header::make('Bahan')->width('50%'),
                                        Header::make('Spesifikasi')->width('50%'),
                                    ])
                                    ->schema([
                                        TextInput::make('key')->label('Bahan')
                                            ->extraAttributes([
                                                'style' => 'border: none !important; border-radius: 0 !important;',
                                            ]),
                                        TextInput::make('value')->label('Spesifikasi')
                                            ->extraAttributes([
                                                'style' => 'border: none !important; border-radius: 0 !important;',
                                            ]),
                                    ])
                                    ->default(function (callable $get) use ($defaultSpecifications) {
                                        $productName = strtolower($get('product_name') ?? 'pantry');

                                        return $defaultSpecifications[$productName]['product_spesification'] ?? [];
                                    })
                                    ->minItems(1)
                                    ->addActionLabel('Tambah Data')
                                    ->columnSpanFull(),

                                TableRepeater::make('material_thickness_spesification')
                                    ->streamlined()
                                    ->label('Ketebalan Material')
                                    ->headers([
                                        Header::make('Komponen')->width('50%'),
                                        Header::make('Tebal')->width('50%'),
                                    ])
                                    ->schema([
                                        TextInput::make('key')->label('Komponen')
                                            ->extraAttributes([
                                                'style' => 'border: none !important; border-radius: 0 !important;',
                                            ]),
                                        TextInput::make('value')->label('Tebal')
                                            ->extraAttributes([
                                                'style' => 'border: none !important; border-radius: 0 !important;',
                                            ]),
                                    ])
                                    ->default([])
                                    ->addActionLabel('Tambah Data')
                                    ->columnSpanFull(),

                                TableRepeater::make('coating_standard')
                                    ->streamlined()
                                    ->label('Standar Lapisan')
                                    ->headers([
                                        Header::make('Kategori')->width('50%'),
                                        Header::make('Jenis')->width('50%'),
                                    ])
                                    ->schema([
                                        SelectComponents::materialCategorySelect("Kategori"),
                                        SelectComponents::materialNameSelect("Jenis"),
                                    ])
                                    ->default([])
                                    ->minItems(1)
                                    ->addActionLabel('Tambah Data')
                                    ->columnSpanFull(),

                                TableRepeater::make('coating_spesification')
                                    ->streamlined()
                                    ->label('Spesifikasi Lapisan')
                                    ->streamlined()
                                    ->headers([
                                        Header::make('Kategori')->width('20%'),
                                        Header::make('Jenis')->width('55%'),
                                        Header::make('Val')->width('5%'),
                                        Header::make('Note')->width('30%'),
                                    ])
                                    ->schema([
                                        SelectComponents::materialCategorySelect("Kategori"),
                                        SelectComponents::materialNameSelect("Jenis"),
                                        TextInput::make('val')->label('Val')
                                            ->extraAttributes([
                                                'style' => 'border: none !important; border-radius: 0 !important;',
                                            ]),
                                        TextInput::make('note')->label('Note')
                                            ->extraAttributes([
                                                'style' => 'border: none !important; border-radius: 0 !important;',
                                            ]),
                                    ])
                                    ->default([])
                                    ->minItems(1)
                                    ->addActionLabel('Tambah Data')
                                    ->columnSpanFull(),

                                TableRepeater::make('komp_anodize_spesification')
                                    ->streamlined()
                                    ->label('Komp / Anodize')
                                    ->headers([
                                        Header::make('Kategori')->width('50%'),
                                        Header::make('Nama barang')->width('50%'),
                                    ])
                                    ->schema([
                                        SelectComponents::materialCategorySelect("Kategori"),
                                        SelectComponents::materialNameSelect("Nama barang"),
                                    ])
                                    ->default([])
                                    ->minItems(1)
                                    ->addActionLabel('Tambah Data')
                                    ->columnSpanFull(),

                                TableRepeater::make('alu_frame_spesification')
                                    ->streamlined()
                                    ->label('Aluminium Frame')
                                    ->headers([
                                        Header::make('Kategori')->width('50%'),
                                        Header::make('Nama barang')->width('50%'),
                                    ])
                                    ->schema([
                                        SelectComponents::materialCategorySelect("Kategori"),
                                        SelectComponents::materialNameSelect("Nama barang"),
                                    ])
                                    ->default([])
                                    ->minItems(1)
                                    ->addActionLabel('Tambah Data')
                                    ->columnSpanFull(),

                                TableRepeater::make('hinges_spesification')
                                    ->streamlined()
                                    ->label('Spesifikasi Engsel')
                                    ->headers([
                                        Header::make('Kategori')->width('50%'),
                                        Header::make('Nama barang')->width('50%'),
                                    ])
                                    ->schema([
                                        SelectComponents::materialCategorySelect("Kategori"),
                                        SelectComponents::materialNameSelect("Nama barang"),
                                    ])
                                    ->default([])
                                    ->minItems(1)
                                    ->addActionLabel('Tambah Data')
                                    ->columnSpanFull(),

                                TableRepeater::make('rail_spesification')
                                    ->streamlined()
                                    ->label('Spesifikasi Rel Laci')
                                    ->headers([
                                        Header::make('Kategori')->width('50%'),
                                        Header::make('Nama barang')->width('50%'),
                                    ])
                                    ->schema([
                                        SelectComponents::materialCategorySelect("Kategori"),
                                        SelectComponents::materialNameSelect("Nama barang"),
                                    ])
                                    ->default([])
                                    ->minItems(1)
                                    ->addActionLabel('Tambah Rel')
                                    ->columnSpanFull(),

                                TableRepeater::make('door_mechanism')
                                    ->streamlined()->label('Spesifikasi Door Mechanism')
                                    ->headers([
                                        Header::make('Tipe')->width('50%'),
                                        Header::make('Nama barang')->width('50%'),
                                    ])
                                    ->schema([
                                        TextInput::make('key')->label('Tipe')->extraAttributes([
                                            'style' => 'border: none !important; border-radius: 0 !important;',
                                        ]),
                                        Select::make('value')->label('Nama barang')->options(Material::pluck('name', 'name'))->searchable()->extraAttributes([
                                            'style' => 'border: none !important; border-radius: 0 !important;',
                                        ]),
                                    ])
                                    ->default([])
                                    ->minItems(2)
                                    ->addActionLabel('Tambah Data')
                                    ->columnSpanFull(),

                                TableRepeater::make('glass_spesification')
                                    ->streamlined()
                                    ->label('Kaca')
                                    ->headers([
                                        Header::make('Tipe')->width('50%'),
                                        Header::make('Nama barang')->width('50%'),
                                    ])
                                    ->schema([
                                        TextInput::make('key')->label('Tipe')->extraAttributes([
                                            'style' => 'border: none !important; border-radius: 0 !important;',
                                        ]),
                                        Select::make('value')->label('Nama barang')->options(Material::pluck('name', 'name'))->searchable()->extraAttributes([
                                            'style' => 'border: none !important; border-radius: 0 !important;',
                                        ]),
                                    ])
                                    ->default([])
                                    ->minItems(5)
                                    ->addActionLabel('Tambah Data')
                                    ->columnSpanFull(),

                                TableRepeater::make('profile_spesification')
                                    ->streamlined()
                                    ->label('Profil')
                                    ->headers([
                                        Header::make('Kategori')->width('50%'),
                                        Header::make('Nama barang')->width('50%'),
                                    ])
                                    ->schema([
                                        SelectComponents::materialCategorySelect("Kategori"),
                                        SelectComponents::materialNameSelect("Nama barang"),
                                    ])
                                    ->default([])
                                    ->minItems(1)
                                    ->addActionLabel('Tambah Data')
                                    ->columnSpanFull(),

                                TableRepeater::make('size_distance_spesification')
                                    ->streamlined()
                                    ->label('Referensi Jarak Ukuran')
                                    ->headers([
                                        Header::make('Komponen')->width('50%'),
                                        Header::make('Jarak (mm)')->width('50%'),
                                    ])
                                    ->schema([
                                        TextInput::make('key')->label('Komponen')->extraAttributes([
                                            'style' => 'border: none !important; border-radius: 0 !important;',
                                        ]),
                                        TextInput::make('value')->label('Jarak (mm)')->extraAttributes([
                                            'style' => 'border: none !important; border-radius: 0 !important;',
                                        ]),
                                    ])
                                    ->default([])
                                    ->minItems(1)
                                    ->addActionLabel('Tambah Ukuran')
                                    ->columnSpanFull(),
                            ])
                            ->columns(1),
                        Select::make('modul_reference')
                            ->label('Referensi Modul')
                            ->multiple()
                            ->options(Modul::pluck('code_cabinet', 'code_cabinet')->toArray())
                            ->searchable()
                            ->preload()
                            ->reactive()
                            ->required()
                            ->hint('Pilih satu atau lebih modul sebagai referensi')
                            ->createOptionForm([
                                Section::make('Informasi Data')
                                    ->schema($textInputs)
                                    ->collapsible(),
                                TextInput::make('code_cabinet')
                                    ->label('Kode Cabinet')
                                    ->readOnly()
                                    ->dehydrated(true)
                                    ->required()
                                    ->rule('unique:moduls,code_cabinet')
                                    ->validationMessages([
                                        'unique' => 'Kode Cabinet tersebut sudah pernah dibuat.',
                                        'required' => 'Kode Cabinet wajib diisi.',
                                    ]),
                                Grid::make(2)->schema(
                                    collect($selectFields)->map(function ($config, $field) {
                                        return Select::make($field)
                                            ->label($config['label'])
                                            ->options(fn() => $config['model']::pluck('name', 'name'))
                                            ->searchable()
                                            ->reactive()
                                            ->afterStateUpdated(function (callable $set, callable $get) {
                                                $set('code_cabinet', generateCabinetCode(
                                                    $get('description_unit'),
                                                    $get('box_carcase_shape'),
                                                    finishing: $get('finishing'),
                                                    layerposition: $get('layer_position'),
                                                    boxContent: $get('box_carcase_content'),
                                                    closingSystem: $get('closing_system'),
                                                    numberOfClosures: $get('number_of_closures'),
                                                    typeOfClosure: $get('type_of_closure'),
                                                    handle: $get('handle'),
                                                    accessories: $get('acc'),
                                                    lamp: $get('lamp'),
                                                    plinth: $get('plinth'),
                                                ));
                                            });
                                    })->toArray()
                                )
                            ])
                            ->createOptionUsing(function (array $data) {
                                $modul = Modul::create($data);
                                return $modul->code_cabinet;
                            })
                            ->createOptionAction(function ($record, callable $set, callable $get) {
                                if (! $record instanceof Modul) {
                                    return;
                                }

                                $existing = $get('modul_reference');
                                if (!is_array($existing)) {
                                    $existing = [];
                                }

                                $existing = array_unique(array_merge($existing, [$record->id]));
                                $set('modul_reference', $existing);
                            })
                    ]),

                // Step 2: Breakdown Modul + Komponen
                Wizard\Step::make('Breakdown Modul')
                    ->schema([
                        Livewire::make('komponen-table')
                            ->data(fn($get, $livewire) => [
                                'moduls' => $get('modul_reference') ?? [],
                                'recordId' => $livewire->getRecord()?->id,
                            ])
                    ]),
            ])
                ->startOnStep(
                    request()->routeIs('filament.admin.resources.projects.edit') && request()->get('step') === '2' ? 2 : 1
                )
                ->columnSpanFull(),
        ];
    }
}
