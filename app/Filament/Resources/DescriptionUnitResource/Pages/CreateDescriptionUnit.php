<?php

namespace App\Filament\Resources\DescriptionUnitResource\Pages;

use App\Filament\Resources\DescriptionUnitResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDescriptionUnit extends CreateRecord
{
    protected static string $resource = DescriptionUnitResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
