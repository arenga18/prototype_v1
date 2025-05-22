<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Filament\Resources\ProjectResource\RelationManagers;
use App\Models\Material;
use App\Models\Project;
use App\Models\PartComponent;
use App\Models\ModulComponent;
use App\Models\Accessories;
use App\Models\BoxCarcaseShape;
use App\Models\ClosingSystem;
use App\Models\DescriptionUnit;
use App\Models\Finishing;
use App\Models\Handle;
use App\Models\LayerPosition;
use App\Models\Modul;
use App\Models\NumberOfClosure;
use App\Models\TypeOfClosure;
use App\Models\Lamp;
use App\Models\Plinth;
use Filament\Forms;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Livewire;
use Filament\Forms\Components\KeyValue;
// use Filament\Forms\Actions\ButtonAction;
// use Filament\Forms\Components\Actions\Button;
use Icetalker\FilamentTableRepeater\Forms\Components\TableRepeater;
use Filament\Forms\Components\Wizard;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;



class ProjectResource extends Resource
{

    protected static $spesifikasiFields = [
        'product_spesification' => 'Spesifikasi Produk',
        'material_thickness_spesification' => 'Ketebalan Material',
        'coating_spesification' => 'Coating',
        'alu_frame_spesification' => 'Aluminium Frame',
        'hinges_spesification' => 'Engsel (Hinges)',
        'rail_spesification' => 'Rel (Rail)',
        'glass_spesification' => 'Kaca',
        'profile_spesification' => 'Profil',
        'size_distance_spesification' => 'Jarak Ukuran',
    ];

    protected static $selectFields = [
        'description_unit' => ['label' => 'Deskripsi Unit', 'model' => DescriptionUnit::class],
        'box_carcase_shape' => ['label' => 'Bentuk Box/Carcase', 'model' => BoxCarcaseShape::class],
        'finishing' => ['label' => 'Finishing', 'model' => Finishing::class],
        'layer_position' => ['label' => 'Posisi Lapisan', 'model' => LayerPosition::class],
        'closing_system' => ['label' => 'Sistem Tutup', 'model' => ClosingSystem::class],
        'number_of_closures' => ['label' => 'Jumlah Tutup', 'model' => NumberOfClosure::class],
        'type_of_closure' => ['label' => 'Jenis Tutup', 'model' => TypeOfClosure::class],
        'handle' => ['label' => 'Handle', 'model' => Handle::class],
        'acc' => ['label' => 'Accessories', 'model' => Accessories::class],
        'lamp' => ['label' => 'Lampu', 'model' => Lamp::class],
        'plinth' => ['label' => 'Plinth', 'model' => Plinth::class],
    ];

    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = "Projek";

    protected static ?string $pluralLabel = "Projek";

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        $textInputs = [
            DatePicker::make('input_date')->label('Tanggal Input')->required(),
            TextInput::make('nip')->label('NIP')->required(),
            TextInput::make('height')->label('Tinggi')->required(),
            TextInput::make('project_name')->label('Nama Proyek')->required(),
            TextInput::make('product_name')->label('Nama Produk')->required(),
        ];

        return $form->schema([
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

                                TextInput::make('product_name')->label('Nama Produk')->required(),
                                TextInput::make('project_name')->label('Nama Proyek')->required(),
                                TextInput::make('estimator')->label('Estimator ')->required(),
                                TextInput::make('recap_coordinator')->label('Koordinator Rekap')->required(),
                                Forms\Components\CheckboxList::make('project_status')
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
                                    ->label('Spesifikasi Produk')
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
                                    ->default([
                                        ['key' => 'Kabinet 1', 'value' => ''],
                                        ['key' => 'Kabinet 2', 'value' => ''],
                                        ['key' => 'Kabinet 3', 'value' => ''],
                                        ['key' => 'Kabinet 4', 'value' => ''],
                                        ['key' => 'Kabinet 5', 'value' => ''],
                                    ])
                                    ->minItems(1)
                                    ->addActionLabel('Tambah Data')
                                    ->columnSpanFull(),
                                TableRepeater::make('material_thickness_spesification')
                                    ->label('Ketebalan Material')
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
                                    ->default([
                                        ['key' => 'Komponen Kabinet', 'value' => ''],
                                        ['key' => 'Komponen Laci', 'value' => ''],
                                        ['key' => 'Dinding blk/dasar', 'value' => ''],
                                        ['key' => 'Tatakan S/s', 'value' => ''],
                                        ['key' => 'Tatakan Worktop', 'value' => ''],
                                    ])
                                    ->minItems(5)
                                    ->addActionLabel('Tambah Data')
                                    ->columnSpanFull(),
                                TableRepeater::make('coating_spesification')
                                    ->label('Coating')
                                    ->schema([
                                        TextInput::make('key')->label('Komponen')
                                            ->extraAttributes([
                                                'style' => 'border: none !important; border-radius: 0 !important;',
                                            ]),
                                        TextInput::make('value')->label('Lapisan')
                                            ->extraAttributes([
                                                'style' => 'border: none !important; border-radius: 0 !important;',
                                            ]),
                                    ])
                                    ->default([
                                        ['key' => 'Lapisan dibalik pintu', 'value' => ''],
                                        ['key' => 'Lapisan tidak terlihat u/kab', 'value' => ''],
                                        ['key' => 'Lapisan depan bhn pintu mlp', 'value' => ''],
                                    ])
                                    ->minItems(1)
                                    ->addActionLabel('Tambah Data')
                                    ->columnSpanFull(),
                                TableRepeater::make('komp_anodize_spesification')
                                    ->label('Komp / Anodize')
                                    ->schema([
                                        Select::make('key')
                                            ->label('Bahan')
                                            ->options(
                                                Material::all()->mapWithKeys(fn($item) => [(string) $item->cat => $item->cat])
                                            )->searchable()->extraAttributes([
                                                'style' => 'border: none !important; border-radius: 0 !important;',
                                            ]),
                                        Select::make('value')->label('Nama barang')->options(Material::pluck('name', 'name'))->searchable()->extraAttributes([
                                            'style' => 'border: none !important; border-radius: 0 !important;',
                                        ]),
                                    ])
                                    ->minItems(1)
                                    ->addActionLabel('Tambah Data')
                                    ->columnSpanFull(),
                                TableRepeater::make('alu_frame_spesification')
                                    ->label('Aluminium Frame')
                                    ->schema([
                                        Select::make('key')->label('Tipe')->options(
                                            Material::all()->mapWithKeys(fn($item) => [(string) $item->cat => $item->cat])
                                        )->searchable()->extraAttributes([
                                            'style' => 'border: none !important; border-radius: 0 !important;',
                                        ]),
                                        Select::make('value')->label('Nama barang')->options(Material::pluck('name', 'name'))->searchable()->extraAttributes([
                                            'style' => 'border: none !important; border-radius: 0 !important;',
                                        ]),
                                    ])
                                    ->minItems(1)
                                    ->addActionLabel('Tambah Data')
                                    ->columnSpanFull(),
                                TableRepeater::make('hinges_spesification')
                                    ->label('Engsel (Hinges)')
                                    ->schema([
                                        Select::make('key')->label('Tipe')->options(
                                            Material::all()->mapWithKeys(fn($item) => [(string) $item->cat => $item->cat])
                                        )->searchable()->extraAttributes([
                                            'style' => 'border: none !important; border-radius: 0 !important;',
                                        ]),
                                        Select::make('value')->label('Nama barang')->options(Material::pluck('name', 'name'))->searchable()->extraAttributes([
                                            'style' => 'border: none !important; border-radius: 0 !important;',
                                        ]),
                                    ])
                                    ->minItems(1)
                                    ->addActionLabel('Tambah Data')
                                    ->columnSpanFull(),
                                TableRepeater::make('rail_spesification')
                                    ->label('Rel (Rail)')
                                    ->schema([
                                        Select::make('key')->label('Tipe')->options(
                                            Material::all()->mapWithKeys(fn($item) => [(string) $item->cat => $item->cat])
                                        )->searchable()->extraAttributes([
                                            'style' => 'border: none !important; border-radius: 0 !important;',
                                        ]),
                                        Select::make('value')->label('Nama barang')->options(Material::pluck('name', 'name'))->searchable()->extraAttributes([
                                            'style' => 'border: none !important; border-radius: 0 !important;',
                                        ]),
                                    ])
                                    ->minItems(1)
                                    ->addActionLabel('Tambah Rel')
                                    ->columnSpanFull(),
                                TableRepeater::make('glass_spesification')
                                    ->label('Kaca')
                                    ->schema([
                                        TextInput::make('key')->label('Tipe')->extraAttributes([
                                            'style' => 'border: none !important; border-radius: 0 !important;',
                                        ]),
                                        Select::make('value')->label('Nama barang')->options(Material::pluck('name', 'name'))->searchable()->extraAttributes([
                                            'style' => 'border: none !important; border-radius: 0 !important;',
                                        ]),
                                    ])
                                    ->default([
                                        ['key' => 'Kaca type 1', 'value' => ''],
                                        ['key' => 'Kaca type 2', 'value' => ''],
                                        ['key' => 'Kaca type 3', 'value' => ''],
                                        ['key' => 'Kaca type 4', 'value' => ''],
                                        ['key' => 'Kaca type 5', 'value' => ''],
                                    ])
                                    ->minItems(5)
                                    ->addActionLabel('Tambah Data')
                                    ->columnSpanFull(),
                                TableRepeater::make('profile_spesification')
                                    ->label('Profil')
                                    ->schema([
                                        Select::make('key')->label('Kategori')->options(
                                            Material::all()->mapWithKeys(fn($item) => [(string) $item->cat => $item->cat])
                                        )->searchable()->extraAttributes([
                                            'style' => 'border: none !important; border-radius: 0 !important;',
                                        ]),
                                        Select::make('value')->label('Nama barang')->options(Material::pluck('name', 'name'))->searchable()->extraAttributes([
                                            'style' => 'border: none !important; border-radius: 0 !important;',
                                        ]),
                                    ])
                                    ->minItems(1)
                                    ->addActionLabel('Tambah Data')
                                    ->columnSpanFull(),
                                TableRepeater::make('size_distance_spesification')
                                    ->label('Jarak Ukuran')
                                    ->schema([
                                        TextInput::make('key')->label('Komponen')->extraAttributes([
                                            'style' => 'border: none !important; border-radius: 0 !important;',
                                        ]),
                                        TextInput::make('value')->label('Jarak (mm)')->extraAttributes([
                                            'style' => 'border: none !important; border-radius: 0 !important;',
                                        ]),
                                    ])
                                    ->minItems(1)
                                    ->addActionLabel('Tambah Ukuran')
                                    ->columnSpanFull(),
                            ])
                            ->columns(1),

                        Select::make('modul_reference')
                            ->label('Referensi Modul')
                            ->multiple()
                            ->options(Modul::pluck('code_cabinet', 'id')->toArray())
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
                                    collect(self::$selectFields)->map(function ($config, $field) {
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
                                return $modul->id;
                            })
                            ->createOptionAction(function ($record, callable $set, callable $get) {
                                // Pastikan record adalah Modul
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
                            ->data(function ($get) {
                                return [
                                    'moduls' => $get('modul_reference') ?? [],
                                ];
                            }),
                    ]),
            ])
                ->nextAction(
                    fn(Action $action) => $action->label(''),
                )
                ->startOnStep(
                    request()->routeIs('filament.admin.resources.projects.edit') && request()->get('step') === '2' ? 2 : 1
                )
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')->label('Tanggal'),
                TextColumn::make('recap_number')->label('No Rekap'),
                TextColumn::make('no_contract')->label('No Kontrak'),
                TextColumn::make('nip')->label('NIP'),
                TextColumn::make('product_name')->label('Nama Produk'),
                TextColumn::make('project_name')->label('Nama Projek'),
                TextColumn::make('estimator')->label('Estimator'),
                TextColumn::make('recap_coordinator')->label('Koordinator Rekap'),
            ])
            ->searchable()
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
        ];
    }
}
