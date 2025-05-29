<?php

namespace App\Filament\Resources\ModulComponentResource\Pages;

use App\Filament\Resources\ModulComponentResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateModulComponent extends CreateRecord
{
    protected static string $resource = ModulComponentResource::class;

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction(),
            $this->getCancelFormAction(),
        ];
    }

    protected function getCreateFormAction(): Action
    {
        return Action::make('create')
            ->action(function (array $data) {

                // Tambahkan notifikasi sukses di sini
                Notification::make()
                    ->title('Data berhasil dibuat!')
                    ->success()
                    ->send();

                // Setelah selesai, redirect manual
                $this->redirect($this->getResource()::getUrl('index'));
            })
            ->keyBindings(['mod+s']);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
