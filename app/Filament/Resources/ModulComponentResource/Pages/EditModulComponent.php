<?php

namespace App\Filament\Resources\ModulComponentResource\Pages;

use App\Filament\Resources\ModulComponentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditModulComponent extends EditRecord
{
    protected static string $resource = ModulComponentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
