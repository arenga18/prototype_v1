<?php

namespace App\Livewire;

use Livewire\Component;
use Filament\Tables;
use Filament\Forms;
use Filament\Support\Contracts\TranslatableContentDriver;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Hidden;
use App\Models\ModulComponent;

class KomponenTable extends Component implements HasTable, HasForms
{
    use InteractsWithTable, InteractsWithForms;

    public $moduls = [];

    public function mount($moduls)
    {
        $this->moduls = $moduls ?? [];
    }

    public function makeFilamentTranslatableContentDriver(): ?TranslatableContentDriver
    {
        return null;
    }

    protected function getTableQuery()
    {
        $decodedModuls = array_map(function ($item) {
            return json_decode('"' . $item . '"');
        }, $this->moduls);

        return ModulComponent::query()
            ->whereIn('modul', $decodedModuls);
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('modul')->label('Modul')->sortable()->searchable(),
            Tables\Columns\TextColumn::make('component')->label('Nama Komponen')->sortable()->searchable(),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ];
    }

    protected function getTableBulkActions(): array
    {
        return [
            Tables\Actions\DeleteBulkAction::make(),
        ];
    }

    protected function getTableHeaderActions(): array
    {
        return [
            Tables\Actions\CreateAction::make()
                ->modalHeading('Tambah Komponen Modul')
                ->form([
                    TextInput::make('component')->required(),
                    Textarea::make('description'),
                    TextInput::make('quantity')->numeric()->default(1)->minValue(1),
                    Hidden::make('modul')->default(fn() => is_array($this->moduls[0]) ? ($this->moduls[0]['modul_reference'] ?? null) : $this->moduls[0]),
                ])
                ->action(function (array $data): void {
                    ModulComponent::create($data);
                }),
        ];
    }

    public function render()
    {
        return view('livewire.komponen-table');
    }
}
