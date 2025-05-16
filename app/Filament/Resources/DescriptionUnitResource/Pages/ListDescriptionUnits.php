<?php

namespace App\Filament\Resources\DescriptionUnitResource\Pages;

use App\Filament\Resources\DescriptionUnitResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDescriptionUnits extends ListRecords
{
    protected static string $resource = DescriptionUnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
