<?php

namespace App\Filament\Resources\ModulComponentResource\Pages;

use App\Filament\Resources\ModulComponentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListModulComponents extends ListRecords
{
    protected static string $resource = ModulComponentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
