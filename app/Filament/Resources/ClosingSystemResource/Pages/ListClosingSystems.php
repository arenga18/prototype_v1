<?php

namespace App\Filament\Resources\ClosingSystemResource\Pages;

use App\Filament\Resources\ClosingSystemResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListClosingSystems extends ListRecords
{
    protected static string $resource = ClosingSystemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
