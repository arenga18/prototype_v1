<?php

namespace App\Filament\Resources\LayerPositionResource\Pages;

use App\Filament\Resources\LayerPositionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLayerPosition extends EditRecord
{
    protected static string $resource = LayerPositionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
