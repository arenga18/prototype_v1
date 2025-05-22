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
                TextInput::make('number_of_sub')->label('Jml sub'),
                TextInput::make('material')->label('Bahan'),
                TextInput::make('thickness')->label('Tebal'),
                TextInput::make('minifix')->label('Minifix'),
                TextInput::make('dowel')->label('Dowel'),
                TextInput::make('elbow_type')->label('Tipe Siku'),
                TextInput::make('screw_type')->label('Tipu Screw'),
                TextInput::make('V')->label('V'),
                TextInput::make('V2')->label('V2'),
                TextInput::make('H')->label('H'),
                TextInput::make('profile3')->label('Profil 3'),
                TextInput::make('profile2')->label('Profil 2'),
                TextInput::make('profile')->label('Profil'),
                TextInput::make('outside')->label('L'),
                TextInput::make('inside')->label('D'),
                TextInput::make('P1')->label('P1'),
                TextInput::make('P2')->label('P2'),
                TextInput::make('L1')->label('L1'),
                TextInput::make('L2')->label('L2'),
                TextInput::make('rail')->label('Rel'),
                TextInput::make('hinge')->label('Engsel'),
                TextInput::make('anodize')->label('Anodize'),
                TextInput::make('number_of_anodize')->label('Jumlah Anodize'),
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
