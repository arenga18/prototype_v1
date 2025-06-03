<?php

namespace App\Filament\Resources\ProjectResource\Forms;

use Filament\Forms\Components\Select;
use Filament\Forms\Get;
use App\Models\Material;

class SelectComponents
{
    public static function materialCategorySelect($label): Select
    {
        return Select::make('key')
            ->label($label)
            ->options(
                Material::all()->mapWithKeys(fn($item) => [(string) $item->cat => $item->cat])
            )
            ->searchable()
            ->live()
            ->extraAttributes([
                'style' => 'border: none !important; border-radius: 0 !important;',
            ]);
    }

    public static function materialNameSelect($label): Select
    {
        return Select::make('value')
            ->label($label)
            ->options(function (Get $get) {
                $selectedCat = $get('key');
                if (!$selectedCat) {
                    return Material::pluck('name', 'name');
                }
                return Material::where('cat', $selectedCat)->pluck('name', 'name');
            })
            ->searchable()
            ->extraAttributes([
                'style' => 'border: none !important; border-radius: 0 !important;',
            ]);
    }
}
