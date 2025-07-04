<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Filament\Resources\ProjectResource\Forms\ProjectFormSchema;
use App\Models\Project;
use App\Models\Accessories;
use App\Models\BoxCarcaseContent;
use App\Models\BoxCarcaseShape;
use App\Models\ClosingSystem;
use App\Models\DescriptionUnit;
use App\Models\Finishing;
use App\Models\Handle;
use App\Models\LayerPosition;
use App\Models\NumberOfClosure;
use App\Models\TypeOfClosure;
use App\Models\Lamp;
use App\Models\Plinth;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;


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
        'box_carcase_content' => ['label' => 'Isi Box/Carcase', 'model' => BoxCarcaseContent::class],
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
        return $form->schema(ProjectFormSchema::getSchema());
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
