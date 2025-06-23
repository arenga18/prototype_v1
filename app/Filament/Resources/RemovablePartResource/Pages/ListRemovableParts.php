<?php

namespace App\Filament\Resources\RemovablePartResource\Pages;

use App\Filament\Resources\RemovablePartResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRemovableParts extends ListRecords
{
    protected static string $resource = RemovablePartResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
