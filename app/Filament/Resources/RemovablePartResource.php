<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RemovablePartResource\Pages;
use App\Filament\Resources\RemovablePartResource\RelationManagers;
use App\Models\RemovablePart;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Livewire;

class RemovablePartResource extends Resource
{
    protected static ?string $model = RemovablePart::class;
    protected static ?string $navigationIcon = 'heroicon-o-circle-stack';

    protected static ?string $navigationGroup = "Master data";

    protected static ?string $navigationLabel = "Part Lepasan";

    protected static ?string $pluralLabel = "Part Lepasan";

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Livewire::make('removable-part-livewire')
                    ->data(fn($get, $livewire) => [
                        'recordId' => $livewire->getRecord()?->id,
                    ])
            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('part')
                    ->searchable(),
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
            'index' => Pages\ListRemovableParts::route('/'),
            'create' => Pages\CreateRemovablePart::route('/create'),
            'edit' => Pages\EditRemovablePart::route('/{record}/edit'),
        ];
    }
}
