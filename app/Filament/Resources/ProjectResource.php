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
                // Step 1: Informasi Proyek dan Referensi Modul
                Wizard\Step::make('Informasi Proyek')
                    ->schema([
                        Section::make('Informasi Dasar')
                            ->schema([
                                TextInput::make('no_contract')->label('No Kontrak')->required(),
                                TextInput::make('nip')->label('NIP')->required(),
                                TextInput::make('product_name')->label('Nama Produk')->required(),
                                TextInput::make('project_name')->label('Nama Proyek')->required(),
                                TextInput::make('coordinator')->label('Koordinator')->required(),
                                TextInput::make('recap_coordinator')->label('Koordinator Rekap')->required(),
                                Forms\Components\CheckboxList::make('project_status')
                                    ->label('Status Proyek')
                                    ->options([
                                        'Pendingin' => 'Pendingin',
                                        'Anti Rayap' => 'Anti Rayap',
                                    ])
                                    ->required(),
                            ])->columns(2),
                            
                        Section::make('Spesifikasi')
                            ->schema([
                                KeyValue::make('product_spesification')
                                    ->label('Spesifikasi Produk')
                                    ->keyLabel('Nama')
                                    ->valueLabel('Nilai')
                                    ->addButtonLabel('Tambah Spesifikasi'),
                                
                                KeyValue::make('material_thickness_spesification')
                                    ->label('Ketebalan Material')
                                    ->keyLabel('Jenis')
                                    ->valueLabel('Tebal')
                                    ->addButtonLabel('Tambah Ketebalan'),

                                    
                                
                                KeyValue::make('coating_spesification')
                                    ->label('Coating')
                                    ->keyLabel('Jenis')
                                    ->valueLabel('Keterangan')
                                    ->addButtonLabel('Tambah Coating'),
                                
                                KeyValue::make('alu_frame_spesification')
                                    ->label('Aluminium Frame')
                                    ->keyLabel('Nama')
                                    ->valueLabel('Spesifikasi'),
                                
                                KeyValue::make('hinges_spesification')
                                    ->label('Engsel (Hinges)')
                                    ->keyLabel('Posisi')
                                    ->valueLabel('Jenis Engsel'),
                                
                                KeyValue::make('rail_spesification')
                                    ->label('Rel (Rail)')
                                    ->keyLabel('Letak')
                                    ->valueLabel('Tipe Rel'),
                                
                                KeyValue::make('glass_spesification')
                                    ->label('Kaca')
                                    ->keyLabel('Tipe')
                                    ->valueLabel('Detail'),
                                
                                KeyValue::make('profile_spesification')
                                    ->label('Profil')
                                    ->keyLabel('Nama')
                                    ->valueLabel('Ukuran / Bentuk'),
                                
                                KeyValue::make('size_distance_spesification')
                                    ->label('Jarak Ukuran')
                                    ->keyLabel('Komponen')
                                    ->valueLabel('Jarak'),
                            ])
                            ->columns(1),

                        Section::make('Referensi Modul')
                        ->schema([
                            Select::make('modul_reference')
                                ->label('Referensi Modul')
                                ->multiple() // Enable multiple selections (tags input)
                                ->options(Modul::all()->pluck('code_cabinet', 'code_cabinet'))
                                ->searchable()
                                ->preload()
                                ->reactive()
                                ->createOptionForm([]) // Kosongkan jika tidak perlu membuat opsi baru
                                ->hint('Pilih satu atau lebih modul sebagai referensi')
                                ->required(),
                        ]),
                    ]),

                // Step 2: Breakdown Modul + Komponen
               Wizard\Step::make('Breakdown Modul')
                ->schema([
                    Livewire::make('komponen-table')
                        ->data([
                            'moduls' => fn (callable $get) => $get('modul_reference'),  // ambil data dari state form step 1
                        ]),
                ]),
            ])->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Tambahkan kolom tabel sesuai kebutuhan
            ])
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

    /**
     * Saat load data untuk edit form,
     * kita generate modul_breakdowns dari modul_reference,
     * sehingga data komponen dan ukuran dapat tampil di step 2
     */
    public static function mutateFormDataBeforeFill(array $data): array
    {
        $modulReference = $data['modul_reference'] ?? [];

        // Build modul_breakdowns dengan default ukuran 0 dan komponen dari modul_component
        $modulBreakdowns = collect($modulReference)->map(function ($modul) {
            // ambil data modul_component berdasarkan kode modul
            $modulComponent = ModulComponent::where('modul', $modul['modul'])->first();

            $components = [];
            if ($modulComponent) {
                $components = json_decode($modulComponent->component, true);
            }

            return [
                'modul' => $modul['modul'],
                'p' => 0,
                'l' => 0,
                't' => 0,
                'components' => $components,
            ];
        })->toArray();

        $data['modul_breakdowns'] = $modulBreakdowns;

        return $data;
    }

    /**
     * Jangan simpan data modul_breakdowns langsung ke table Project,
     * karena field ini tidak ada di database Project.
     */
    public static function mutateFormDataBeforeCreate(array $data): array
    {
        unset($data['modul_breakdowns']);
        return $data;
    }

    public static function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['modul_breakdowns']);
        return $data;
    }

    public static function buildSpesifikasiRepeater(string $name, string $label): Section
    {
        return Section::make($label)
            ->schema([
                Repeater::make($name)
                    ->schema([
                        Select::make('cat')
                            ->label('Kategori')
                            ->options(PartComponent::all()->pluck('cat', 'cat')->unique()->toArray()),
                        Select::make('name')
                            ->label('Nama Komponen')
                            ->options(PartComponent::all()->pluck('name', 'name')),
                    ])
                    ->columns(2)
                    ->createItemButtonLabel('Tambah Data'),
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
