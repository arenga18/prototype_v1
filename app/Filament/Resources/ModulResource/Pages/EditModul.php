<?php

namespace App\Filament\Resources\ModulResource\Pages;

use App\Filament\Resources\ModulResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Models\Project;

class EditModul extends EditRecord
{
    protected static string $resource = ModulResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected ?string $oldCodeCabinet = null;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Simpan code_cabinet lama dari database
        $this->oldCodeCabinet = $this->record->getOriginal('code_cabinet');

        return $data;
    }
    protected function afterSave(): void
    {
        $modul = $this->record;

        $oldCode = $this->oldCodeCabinet;
        $newCode = $modul->code_cabinet;

        if ($oldCode && $oldCode !== $newCode) {
            $projects = Project::where('nip', $modul->nip)->get();

            foreach ($projects as $project) {
                $references = $project->modul_reference;

                if (is_array($references) && in_array($oldCode, $references)) {
                    $updatedReferences = array_map(function ($item) use ($oldCode, $newCode) {
                        return $item === $oldCode ? $newCode : $item;
                    }, $references);

                    $project->update([
                        'modul_reference' => $updatedReferences
                    ]);
                }
            }
        }
    }
}
