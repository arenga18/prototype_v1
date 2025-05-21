<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TypeOfClosureResource\Pages;
use App\Filament\Resources\TypeOfClosureResource\RelationManagers;
use App\Models\TypeOfClosure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TypeOfClosureResource extends Resource
{
    protected static ?string $model = TypeOfClosure::class;

    protected static ?string $navigationIcon = 'heroicon-o-circle-stack';

    protected static ?string $navigationGroup = "Modul Master data";

    protected static ?string $navigationLabel = "Jenis Tutup";

    protected static ?string $pluralLabel = "Jenis Tutup";

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
            'index' => Pages\ListTypeOfClosures::route('/'),
            'create' => Pages\CreateTypeOfClosure::route('/create'),
            'edit' => Pages\EditTypeOfClosure::route('/{record}/edit'),
        ];
    }
}
