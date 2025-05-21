<?php

namespace App\Filament\Resources\TypeOfClosureResource\Pages;

use App\Filament\Resources\TypeOfClosureResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTypeOfClosure extends EditRecord
{
    protected static string $resource = TypeOfClosureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
