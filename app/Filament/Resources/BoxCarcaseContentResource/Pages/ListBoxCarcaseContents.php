<?php

namespace App\Filament\Resources\BoxCarcaseContentResource\Pages;

use App\Filament\Resources\BoxCarcaseContentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBoxCarcaseContents extends ListRecords
{
    protected static string $resource = BoxCarcaseContentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
