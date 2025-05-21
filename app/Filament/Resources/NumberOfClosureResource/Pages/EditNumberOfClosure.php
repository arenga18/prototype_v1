<?php

namespace App\Filament\Resources\NumberOfClosureResource\Pages;

use App\Filament\Resources\NumberOfClosureResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditNumberOfClosure extends EditRecord
{
    protected static string $resource = NumberOfClosureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
