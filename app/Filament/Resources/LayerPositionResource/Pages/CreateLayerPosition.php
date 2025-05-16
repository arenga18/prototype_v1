<?php

namespace App\Filament\Resources\LayerPositionResource\Pages;

use App\Filament\Resources\LayerPositionResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateLayerPosition extends CreateRecord
{
    protected static string $resource = LayerPositionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
