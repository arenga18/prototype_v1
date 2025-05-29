<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>App Layout</title>
  {{-- @vite('resources/css/app.css') <!-- Atau link Tailwind jika dipakai --> --}}
  @livewireStyles
</head>

<body>
  <div class="p-4">
    <livewire:modul-modal />
  </div>

  @livewireScripts
</body>

</html>
