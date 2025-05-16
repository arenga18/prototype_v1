<?php

namespace App\Filament\Resources\LayerPositionResource\Pages;

use App\Filament\Resources\LayerPositionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLayerPositions extends ListRecords
{
    protected static string $resource = LayerPositionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
