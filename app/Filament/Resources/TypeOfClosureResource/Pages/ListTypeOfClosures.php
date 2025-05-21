<?php

namespace App\Filament\Resources\TypeOfClosureResource\Pages;

use App\Filament\Resources\TypeOfClosureResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTypeOfClosures extends ListRecords
{
    protected static string $resource = TypeOfClosureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
