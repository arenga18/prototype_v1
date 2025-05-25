<?php

namespace App\Filament\Resources\BoxCarcaseContentResource\Pages;

use App\Filament\Resources\BoxCarcaseContentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBoxCarcaseContent extends EditRecord
{
    protected static string $resource = BoxCarcaseContentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
