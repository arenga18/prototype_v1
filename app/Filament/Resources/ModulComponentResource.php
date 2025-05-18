<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ModulComponentResource\Pages;

use App\Models\Modul;
use App\Models\PartComponent;
use App\Models\ModulComponent;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ModulComponentResource extends Resource
{
    protected static ?string $model = ModulComponent::class;

     protected static ?string $navigationIcon = 'heroicon-o-circle-stack';

    protected static ?string $navigationGroup = "Master data";

    protected static ?string $navigationLabel = "Komponen Modul";

    protected static ?string $pluralLabel = "Komponen Modul";

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
    Forms\Components\Select::make('modul')
        ->label('Modul')
        ->options(Modul::all()->pluck('code_cabinet', 'code_cabinet'))
        ->required(),

    Forms\Components\Repeater::make('component')
        ->label('Komponen & Rumus')
        ->schema([
            Forms\Components\Select::make('component')
                ->label('Component')
                ->options(PartComponent::all()->pluck('name', 'name'))
                ->required(),

            Forms\Components\TextInput::make('p')
                ->label('Rumus P')
                ->required(),

            Forms\Components\TextInput::make('l')
                ->label('Rumus L')
                ->required(),

            Forms\Components\TextInput::make('t')
                ->label('Rumus T')
                ->required(),
        ])
        ->columns(4)
        ->createItemButtonLabel('Tambah Komponen')
        ->required(),
]);

    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                 Tables\Columns\TextColumn::make('modul'),
                Tables\Columns\TextColumn::make('component'),
                Tables\Columns\TextColumn::make('formula'),
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
    return [
        'modul' => $data['modul'],
        'component' => json_encode($data['component']),
    ];
}

    public static function mutateFormDataBeforeFill(array $data): array
{
    $data['component'] = json_decode($data['component'] ?? '[]', true);
    return $data;
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
            'index' => Pages\ListModulComponents::route('/'),
            'create' => Pages\CreateModulComponent::route('/create'),
            'edit' => Pages\EditModulComponent::route('/{record}/edit'),
        ];
    }
}
