<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Filament\Resources\ProjectResource\RelationManagers;
use App\Models\Project;
use App\Models\Component;
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
// use Filament\Forms\Actions\ButtonAction;
// use Filament\Forms\Components\Actions\Button;
use Filament\Forms\Components\Wizard;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-circle-stack';

    protected static ?string $navigationLabel = "Projek";

    protected static ?string $pluralLabel = "Projek";

    public static function form(Form $form): Form
    {


    return $form->schema([
        Wizard::make([
            // Step 1: Informasi Proyek
            Wizard\Step::make('Informasi Proyek')
                ->schema([
                    // SECTION 1: Informasi Dasar
                    Forms\Components\Section::make('Informasi Dasar')
                        ->schema([
                            Forms\Components\TextInput::make('no_contract')->label('No Kontrak')->required(),
                            Forms\Components\TextInput::make('nip')->label('NIP')->required(),
                            Forms\Components\TextInput::make('product_name')->label('Nama Produk')->required(),
                            Forms\Components\TextInput::make('project_name')->label('Nama Proyek')->required(),
                            Forms\Components\TextInput::make('coordinator')->label('Koordinator')->required(),
                            Forms\Components\TextInput::make('recap_coordinator')->label('Koordinator Rekap')->required(),
                            Forms\Components\CheckboxList::make('project_status')
                                ->label('Status Proyek')
                                ->options([
                                    'in_progress' => 'Dalam Proses',
                                    'completed' => 'Selesai',
                                    'cancelled' => 'Dibatalkan',
                                ])
                                ->required(),
                        ])
                        ->columns(2),

                    // SECTION 2: Spesifikasi Produk
                    Forms\Components\Section::make('Spesifikasi Produk')
                        ->schema([
                            self::buildSpesifikasiRepeater('product_spesification', 'Spesifikasi Produk'),
                            self::buildSpesifikasiRepeater('material_thickness_spesification', 'Ketebalan Material'),
                            self::buildSpesifikasiRepeater('coating_spesification', 'Coating'),
                            self::buildSpesifikasiRepeater('alu_frame_spesification', 'Aluminium Frame'),
                            self::buildSpesifikasiRepeater('hinges_spesification', 'Engsel (Hinges)'),
                            self::buildSpesifikasiRepeater('rail_spesification', 'Rel (Rail)'),
                            self::buildSpesifikasiRepeater('glass_spesification', 'Kaca'),
                            self::buildSpesifikasiRepeater('profile_spesification', 'Profil'),
                            self::buildSpesifikasiRepeater('size_distance_spesification', 'Jarak Ukuran'),
                        ])
                        ->columns(1),

                    // SECTION 3: Referensi Modul
                    Forms\Components\Section::make('Referensi Modul')
                        ->schema([
                            Forms\Components\Repeater::make('modul_reference')
                                ->label('Referensi Modul')
                                ->schema([
                                    Forms\Components\Select::make('modul')
                                        ->label('Modul')
                                        ->options(Modul::all()->pluck('code_cabinet', 'code_cabinet'))
                                        ->required(),
                                ])
                                ->createItemButtonLabel('Tambah Modul'),
                        ]),
                ]),

            // Step 2: Breakdown Modul
                Wizard\Step::make('Breakdown Modul')
    ->schema([
        Repeater::make('modul_breakdown')
            ->label('Breakdown Komponen')
            ->schema([
                TextInput::make('modul')->label('Kode Modul')->disabled(),
                TextInput::make('p')->label('P')->required(),
                TextInput::make('l')->label('L')->required(),
                TextInput::make('t')->label('T')->required(),
                Placeholder::make('spacer')->content(' '),
                Livewire::make('komponen-table')
                    ->key('komponen')
                    ->reactive()
                    ->afterStateHydrated(function ($component, $state) {
                        $modulCode = $state['modul'] ?? null;

                        if ($modulCode) {
                            $komponen = ModulComponent::where('modul', $modulCode)->get();

                            $component->fill(['komponen' => $komponen->map(function ($item) {
                                return [
                                    'component' => $item->component,
                                    'p_value' => 0,
                                    'l_value' => 0,
                                    't_value' => 0,
                                ];
                            })->toArray()]);
                        }
                    }),
            ])
            ->columns(4),
    ])

        ]),
        // The Wizard component already provides navigation and submit actions by default.
    ]);


    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function mutateFormDataBeforeCreate(array $data): array
{
    unset($data['modul_breakdown']); // hanya untuk preview/edit, tidak disimpan

    return $data;
}

    public static function mutateFormDataBeforeFill(array $data): array
{
    $modulReference = $data['modul_reference'] ?? [];

    $breakdown = collect($modulReference)->flatMap(function ($modul) {
        $modulData = ModulComponent::where('modul', $modul['modul'])->first();

        if (! $modulData) return [];

        $components = json_decode($modulData->component, true);

        return collect($components)->map(function ($c) use ($modul) {
            return [
                'modul' => $modul['modul'],
                'component' => $c['component'] ?? $c['name'] ?? '',
                'p' => $c['p'] ?? '',
                'l' => $c['l'] ?? '',
                't' => $c['t'] ?? '',
            ];
        });
    })->values();

    $data['modul_breakdown'] = $breakdown->toArray();

    return $data;
}

    public static function buildSpesifikasiRepeater(string $name, string $label): Forms\Components\Repeater
    {
    return Forms\Components\Repeater::make($name)
        ->label($label)
        ->schema([
            Forms\Components\Select::make('cat')
                ->label('Kategori')
                ->options(Component::all()->pluck('cat', 'cat')->unique()->toArray())
                ->required(),

            Forms\Components\Select::make('name')
                ->label('Nama Komponen')
                ->options(Component::all()->pluck('name', 'name'))
                ->required(),
        ])
        ->columns(2)
        ->createItemButtonLabel("Tambah {$label}");
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
