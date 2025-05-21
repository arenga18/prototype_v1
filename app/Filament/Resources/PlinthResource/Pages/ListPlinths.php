<?php

namespace App\Filament\Resources\PlinthResource\Pages;

use App\Filament\Resources\PlinthResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPlinths extends ListRecords
{
    protected static string $resource = PlinthResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
