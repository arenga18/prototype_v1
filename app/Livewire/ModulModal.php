<?php

namespace App\Livewire;

use Livewire\Component;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;

class ModulModal extends Component implements HasForms
{
    use InteractsWithForms;

    public $formData = [];

    public function mount()
    {
        $this->form->fill(); // Mengisi form dengan nilai default (kosong)
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')
                    ->label('Judul Modul')
                    ->required(),
                TextInput::make('description')
                    ->label('Deskripsi')
                    ->required(),
            ])
            ->statePath('formData'); // Data form disimpan di $formData
    }

    public function submit()
    {
        $data = $this->form->getState();

        // Lakukan sesuatu dengan data form
        // Contoh: Kirim event ke komponen table
        $this->emit('modulCreated', $data);

        // Atau simpan ke database
        // Modul::create($data);

        // Reset form
        $this->form->fill();

        // Feedback
        session()->flash('success', 'Data Modul berhasil disimpan!');
    }

    public function render()
    {
        return view('livewire.modul-modal');
    }
}
