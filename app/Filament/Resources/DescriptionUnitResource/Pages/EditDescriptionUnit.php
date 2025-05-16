<?php

namespace App\Filament\Resources\DescriptionUnitResource\Pages;

use App\Filament\Resources\DescriptionUnitResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDescriptionUnit extends EditRecord
{
    protected static string $resource = DescriptionUnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
