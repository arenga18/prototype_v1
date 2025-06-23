<?php

namespace App\Filament\Resources\RemovablePartResource\Pages;

use App\Filament\Resources\RemovablePartResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRemovablePart extends EditRecord
{
    protected static string $resource = RemovablePartResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
