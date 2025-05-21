<?php

namespace App\Filament\Resources\LampResource\Pages;

use App\Filament\Resources\LampResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLamp extends EditRecord
{
    protected static string $resource = LampResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
