<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ComponentResource\Pages;
use App\Models\PartComponent;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Livewire;

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
                Livewire::make('part-component-livewire')
                    ->data(fn($livewire) => [
                        'recordId' => $livewire->getRecord()?->id,
                    ])
            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('cat')->label('Kategori')->searchable(),
                TextColumn::make('code')->label('Kode')->searchable(),
                TextColumn::make('name')->label('Nama')->searchable(),
                TextColumn::make('val')->label('Val')->searchable(),
                TextColumn::make('KS')->label('KS')->searchable(),
                TextColumn::make('number_of_sub')->searchable(),
                TextColumn::make('material')->searchable(),
                TextColumn::make('thickness')->searchable(),
                TextColumn::make('minifix')->searchable(),
                TextColumn::make('dowel')->searchable(),
                TextColumn::make('elbow_type')->searchable(),
                TextColumn::make('screw_type')->searchable(),
                TextColumn::make('V')->searchable(),
                TextColumn::make('V2')->searchable(),
                TextColumn::make('H')->searchable(),
                TextColumn::make('profile3')->searchable(),
                TextColumn::make('profile2')->searchable(),
                TextColumn::make('profile')->searchable(),
                TextColumn::make('outside')->searchable(),
                TextColumn::make('inside')->searchable(),
                TextColumn::make('P1')->searchable(),
                TextColumn::make('P2')->searchable(),
                TextColumn::make('L1')->searchable(),
                TextColumn::make('L2')->searchable(),
                TextColumn::make('rail')->searchable(),
                TextColumn::make('hinge')->searchable(),
                TextColumn::make('anodize')->searchable(),
                TextColumn::make('number_of_anodize')->searchable(),
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
