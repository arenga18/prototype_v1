<?php

namespace App\Filament\Resources\BoxCarcaseShapeResource\Pages;

use App\Filament\Resources\BoxCarcaseShapeResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBoxCarcaseShape extends CreateRecord
{
    protected static string $resource = BoxCarcaseShapeResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
