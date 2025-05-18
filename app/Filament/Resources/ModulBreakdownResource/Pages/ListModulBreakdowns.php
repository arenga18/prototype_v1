<?php

namespace App\Filament\Resources\ModulBreakdownResource\Pages;

use App\Filament\Resources\ModulBreakdownResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListModulBreakdowns extends ListRecords
{
    protected static string $resource = ModulBreakdownResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
