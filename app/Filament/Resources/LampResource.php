<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LampResource\Pages;
use App\Filament\Resources\LampResource\RelationManagers;
use App\Models\Lamp;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LampResource extends Resource
{
    protected static ?string $model = Lamp::class;

    protected static ?string $navigationIcon = 'heroicon-o-circle-stack';

    protected static ?string $navigationGroup = "Modul Master data";

    protected static ?string $navigationLabel = "Lamp";

    protected static ?string $pluralLabel = "Lamp";

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name'),
                TextInput::make('code'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('code'),
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
            'index' => Pages\ListLamps::route('/'),
            'create' => Pages\CreateLamp::route('/create'),
            'edit' => Pages\EditLamp::route('/{record}/edit'),
        ];
    }
}
