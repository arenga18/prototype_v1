<?php

namespace App\Filament\Resources\BoxCarcaseShapeResource\Pages;

use App\Filament\Resources\BoxCarcaseShapeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBoxCarcaseShape extends EditRecord
{
    protected static string $resource = BoxCarcaseShapeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
