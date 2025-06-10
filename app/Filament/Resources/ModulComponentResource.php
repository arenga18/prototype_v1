<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ModulComponentResource\Pages;

use App\Models\Modul;
use App\Models\PartComponent;
use Filament\Forms\Components\Section;
use App\Models\ModulComponent;
use Filament\Forms\Components\Select;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Livewire;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Button;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Set;
use Filament\Forms\Get;
use Filament\Facades\Filament;
use Closure;

class ModulComponentResource extends Resource
{
    protected static ?string $model = ModulComponent::class;

    protected static ?string $navigationIcon = 'heroicon-o-circle-stack';

    protected static ?string $navigationGroup = "Master data";

    protected static ?string $navigationLabel = "Komponen Modul";

    protected static ?string $pluralLabel = "Komponen Modul";

    public static function booted(): void
    {
        Filament::registerRenderHook(
            'panels::body.end',
            fn(): string => '<script>console.log("Form sudah dirender!")</script>'
        );
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Livewire::make('komponen-modul')
                    ->data(fn($get, $livewire) => [
                        'modul' => $get('modul') ?? [],
                        'recordId' => $livewire->getRecord()?->id,
                    ])
            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('modul')
                    ->searchable(),
            ])->searchable()
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
            'index' => Pages\ListModulComponents::route('/'),
            'create' => Pages\CreateModulComponent::route('/create'),
            'edit' => Pages\EditModulComponent::route('/{record}/edit'),
        ];
    }
}
