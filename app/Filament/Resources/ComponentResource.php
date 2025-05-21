<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ComponentResource\Pages;
use App\Filament\Resources\ComponentResource\RelationManagers;
use App\Models\PartComponent;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ComponentResource extends Resource
{
    protected static ?string $model = PartComponent::class;

    protected static ?string $navigationIcon = 'heroicon-o-circle-stack';

    protected static ?string $navigationGroup = "Master data";

    protected static ?string $navigationLabel = "Parts Komponen";

    protected static ?string $pluralLabel = "Parts Komponen";

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('cat')->label('Kategori'),
                TextInput::make('code')->label('Kode'),
                TextInput::make('name')->label('Nama'),
                TextInput::make('val')->label('Val'),
                TextInput::make('KS')->label('KS'),
                TextInput::make('number_of_sub'),
                TextInput::make('material'),
                TextInput::make('thickness'),
                TextInput::make('minifix'),
                TextInput::make('dowel'),
                TextInput::make('elbow_type'),
                TextInput::make('screw_type'),
                TextInput::make('V'),
                TextInput::make('V2'),
                TextInput::make('H'),
                TextInput::make('profile3'),
                TextInput::make('profile2'),
                TextInput::make('profile'),
                TextInput::make('outside'),
                TextInput::make('inside'),
                TextInput::make('P1'),
                TextInput::make('P2'),
                TextInput::make('L1'),
                TextInput::make('L2'),
                TextInput::make('rail'),
                TextInput::make('hinge'),
                TextInput::make('anodize'),
                TextInput::make('number_of_anodize'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('cat')->label('Kategori'),
                TextColumn::make('code')->label('Kode'),
                TextColumn::make('name')->label('Nama'),
                TextColumn::make('val')->label('Val'),
                TextColumn::make('KS')->label('KS'),
                TextColumn::make('number_of_sub'),
                TextColumn::make('material'),
                TextColumn::make('thickness'),
                TextColumn::make('minifix'),
                TextColumn::make('dowel'),
                TextColumn::make('elbow_type'),
                TextColumn::make('screw_type'),
                TextColumn::make('V'),
                TextColumn::make('V2'),
                TextColumn::make('H'),
                TextColumn::make('profile3'),
                TextColumn::make('profile2'),
                TextColumn::make('profile'),
                TextColumn::make('outside'),
                TextColumn::make('inside'),
                TextColumn::make('P1'),
                TextColumn::make('P2'),
                TextColumn::make('L1'),
                TextColumn::make('L2'),
                TextColumn::make('rail'),
                TextColumn::make('hinge'),
                TextColumn::make('anodize'),
                TextColumn::make('number_of_anodize'),
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
            'index' => Pages\ListComponents::route('/'),
            'create' => Pages\CreateComponent::route('/create'),
            'edit' => Pages\EditComponent::route('/{record}/edit'),
        ];
    }
}
