<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ModulResource\Pages;
use App\Filament\Resources\ModulResource\RelationManagers;
use App\Models\BoxCarcaseShape;
use App\Models\DescriptionUnit;
use App\Models\Finishing;
use App\Models\LayerPosition;
use App\Models\Modul;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ModulResource extends Resource
{
    protected static ?string $model = Modul::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = "Modul";

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('code_cabinet')
                    ->label('Kode Cabinet')
                    ->readOnly()
                    ->required(),
                Select::make('description_unit')
                    ->label('Deskripsi Unit')
                    ->options(fn() => DescriptionUnit::pluck('name', 'name'))
                    ->reactive()
                    ->searchable()
                    ->afterStateUpdated(
                        fn(callable $set, callable $get, $state) =>
                        $set('code_cabinet', generateCabinetCode(
                            $state,
                            $get('box_carcase_shape'),
                            finishing: $get('finishing'),
                            layerposition: $get('layer_position'),
                        ))
                    ),

                Select::make('box_carcase_shape')
                    ->label('Bentuk Box/Carcase')
                    ->options(fn() => BoxCarcaseShape::pluck('name', 'name'))
                    ->reactive()
                    ->searchable()
                    ->afterStateUpdated(
                        fn(callable $set, callable $get, $state) =>
                        $set('code_cabinet', generateCabinetCode(
                            $get('description_unit'),
                            $state,
                            finishing: $get('finishing'),
                            layerposition: $get('layer_position'),
                        ))
                    ),
                Select::make('finishing')
                    ->label('Finishing')
                    ->options(fn() => Finishing::pluck('name', 'name'))
                    ->reactive()
                    ->searchable()
                    ->afterStateUpdated(
                        fn(callable $set, callable $get, $state) =>
                        $set('code_cabinet', generateCabinetCode(
                            $get('description_unit'),
                            $get('box_carcase_shape'),
                            finishing: $state,
                            layerposition: $get('layer_position'),
                        ))
                    ),
                Select::make('layer_position')
                    ->label('Posisi Lapisan')
                    ->options(fn() => LayerPosition::pluck('name', 'name'))
                    ->reactive()
                    ->searchable()
                    ->afterStateUpdated(
                        fn(callable $set, callable $get, $state) =>
                        $set('code_cabinet', generateCabinetCode(
                            $get('description_unit'),
                            $get('box_carcase_shape'),
                            finishing: $get('finishing'),
                            layerposition: $state,
                        ))
                    ),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code_cabinet'),
                TextColumn::make('description_unit'),
                TextColumn::make('box_carcase_shape'),
                TextColumn::make('finishing'),
                TextColumn::make('layer_position'),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListModuls::route('/'),
            'create' => Pages\CreateModul::route('/create'),
            'edit' => Pages\EditModul::route('/{record}/edit'),
        ];
    }
}
