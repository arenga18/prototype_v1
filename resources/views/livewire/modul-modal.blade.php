<div>
  <form wire:submit.prevent="submit">
    {{ $this->form }}

    <x-filament::button type="submit" color="primary">
      Simpan Modul
    </x-filament::button>
  </form>

  @if (session()->has('success'))
    <div class="mt-4 text-green-600">
      {{ session('success') }}
    </div>
  @endif
</div>
