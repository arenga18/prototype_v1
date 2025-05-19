<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use Filament\Actions;
use Filament\Actions\Action;
use App\Models\Project;
use Filament\Resources\Pages\CreateRecord;

class CreateProject extends CreateRecord
{
    protected static string $resource = ProjectResource::class;

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
            ->label('Next') // Ganti label di sini
            ->submit('create') // Pastikan ini tetap
            ->keyBindings(['mod+s']);
    }


    protected function handleRecordCreation(array $data): Project
    {
        $project = Project::create($data);

        return $project;
    }

    protected function getRedirectUrl(): string
    {
        return route('filament.admin.resources.projects.edit', [
            'record' => $this->record->getKey(),
            'step' => 2,
        ]);
    }
}
