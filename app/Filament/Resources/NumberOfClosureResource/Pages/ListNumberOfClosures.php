<?php

namespace App\Filament\Resources\NumberOfClosureResource\Pages;

use App\Filament\Resources\NumberOfClosureResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListNumberOfClosures extends ListRecords
{
    protected static string $resource = NumberOfClosureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
