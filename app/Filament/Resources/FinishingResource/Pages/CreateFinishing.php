<?php

namespace App\Filament\Resources\FinishingResource\Pages;

use App\Filament\Resources\FinishingResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateFinishing extends CreateRecord
{
    protected static string $resource = FinishingResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
