<?php

namespace App\Filament\Resources\LampResource\Pages;

use App\Filament\Resources\LampResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLamps extends ListRecords
{
    protected static string $resource = LampResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
