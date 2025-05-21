<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Filament\Resources\ProjectResource\RelationManagers;
use App\Models\Project;
use App\Models\PartComponent;
use App\Models\ModulComponent;
use App\Models\Modul;
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
    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = "Projek";

    protected static ?string $pluralLabel = "Projek";



    public static function form(Form $form): Form
    {
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
                                TextInput::make('coordinator')->label('Koordinator')->required(),
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
                            ->schema(
                                collect(self::$spesifikasiFields)->map(function ($label, $fieldName) {
                                    return TableRepeater::make($fieldName)
                                        ->label($label)
                                        ->schema([
                                            Select::make('key')
                                                ->label('Kategori')
                                                ->options(PartComponent::all()->pluck('cat', 'cat'))
                                                ->searchable()
                                                ->extraAttributes([
                                                    'style' => 'border: none !important; border-radius: 0 !important;',
                                                ]),
                                            Select::make('value')
                                                ->label(label: 'Spesifikasi')
                                                ->options(PartComponent::all()->pluck('name', 'name'))
                                                ->searchable()
                                                ->native(false)
                                                ->extraAttributes([
                                                    'style' => 'border: none !important; border-radius: 0 !important;',
                                                ])
                                        ])
                                        ->minItems(1)
                                        ->addActionLabel('Tambah Data')
                                        ->columnSpanFull();
                                })->toArray()
                            )
                            ->columns(1),

                        Section::make('Referensi Modul')
                            ->schema([
                                Select::make('modul_reference')
                                    ->label('Referensi Modul')
                                    ->multiple()
                                    ->options(Modul::all()->pluck('code_cabinet', 'code_cabinet'))
                                    ->searchable()
                                    ->preload()
                                    ->reactive()
                                    ->createOptionForm([])
                                    ->hint('Pilih satu atau lebih modul sebagai referensi')
                                    ->required(),
                            ])
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
