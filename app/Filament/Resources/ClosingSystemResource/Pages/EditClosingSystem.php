<?php

namespace App\Filament\Resources\ClosingSystemResource\Pages;

use App\Filament\Resources\ClosingSystemResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditClosingSystem extends EditRecord
{
    protected static string $resource = ClosingSystemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
