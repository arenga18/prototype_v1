<?php

namespace App\Filament\Resources\FinishingResource\Pages;

use App\Filament\Resources\FinishingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFinishings extends ListRecords
{
    protected static string $resource = FinishingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
