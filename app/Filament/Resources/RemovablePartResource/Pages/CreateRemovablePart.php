<?php

namespace App\Filament\Resources\RemovablePartResource\Pages;

use App\Filament\Resources\RemovablePartResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateRemovablePart extends CreateRecord
{
    protected static string $resource = RemovablePartResource::class;

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
