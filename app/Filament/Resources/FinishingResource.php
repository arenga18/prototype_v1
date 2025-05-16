<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FinishingResource\Pages;
use App\Filament\Resources\FinishingResource\RelationManagers;
use App\Models\Finishing;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FinishingResource extends Resource
{
    protected static ?string $model = Finishing::class;

    protected static ?string $navigationIcon = 'heroicon-o-circle-stack';

    protected static ?string $navigationGroup = "Master data";

    protected static ?string $navigationLabel = "Finishing";

    protected static ?string $pluralLabel = "Finishing";

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
            'index' => Pages\ListFinishings::route('/'),
            'create' => Pages\CreateFinishing::route('/create'),
            'edit' => Pages\EditFinishing::route('/{record}/edit'),
        ];
    }
}
