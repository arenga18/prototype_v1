<?php

namespace App\Filament\Resources\PlinthResource\Pages;

use App\Filament\Resources\PlinthResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPlinth extends EditRecord
{
    protected static string $resource = PlinthResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
