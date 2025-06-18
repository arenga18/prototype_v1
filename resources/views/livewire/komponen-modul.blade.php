  <div style="width: 100%; position: relative;">
    <script src="https://unpkg.com/react@18.3.1/umd/react.production.min.js"></script>
    <script src="https://unpkg.com/react-dom@18.3.1/umd/react-dom.production.min.js"></script>
    <script src="https://unpkg.com/rxjs/dist/bundles/rxjs.umd.min.js"></script>
    <script src="https://unpkg.com/echarts@5.6.0/dist/echarts.min.js"></script>
    <script src="https://unpkg.com/@univerjs/presets/lib/umd/index.js"></script>
    <script src="https://unpkg.com/@univerjs/preset-sheets-core/lib/umd/index.js"></script>
    <script src="https://unpkg.com/@univerjs/preset-sheets-core/lib/umd/locales/en-US.js"></script>
    <script src="https://unpkg.com/@univerjs/preset-sheets-data-validation/lib/umd/index.js"></script>
    <script src="https://unpkg.com/@univerjs/preset-sheets-data-validation/lib/umd/locales/en-US.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/@univerjs/preset-sheets-core/lib/index.css" />
    <link rel="stylesheet" href="https://unpkg.com/@univerjs/preset-sheets-data-validation/lib/index.css" />

    <!-- Scripts tambahan -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href={{ asset('css/style.css') }}>

    <!-- Form controls -->
    <div class="flex w-full gap-x-6 items-center">
      <div class="mb-4 w-full">
        <label for="modulSelect" class="block mb-2 text-sm font-medium text-gray-700">Modul</label>
        <select wire:model="modul" id="modulSelect" class="border border-gray-300 text-sm rounded p-2">
          @if (count($modulList) > 1)
            <option value="">--Pilih Modul--</option>
          @endif

          @forelse($modulList as $m)
            <option value="{{ $m }}" {{ count($modulList) === 1 ? 'selected' : '' }}>{{ $m }}
            </option>
          @empty
            <option value="">Tidak ada modul tersedia</option>
          @endforelse
        </select>
      </div>

      <div class="mb-4 w-full">
        <label for="modulReference" class="block mb-2 text-sm font-medium text-gray-700">Referensi Modul</label>
        <select id="modulReference" name="modulReference">
          <option value="">--Pilih Modul--</option>
          @foreach ($modulReference as $m)
            <option value="{{ $m }}">{{ $m }}</option>
          @endforeach
        </select>
      </div>
      <div class="mb-4">
        <label for="fullscreen-btn" class="block mb-2 text-sm font-medium text-gray-700">Fullscreen</label>
        <button id="fullscreen-btn" type="button"
          style="z-index: 1000; width: fit-content; background: #fff; border: 1px solid #ddd; border-radius: 4px; padding: 5px 10px; cursor: pointer; display: flex; align-items: center; gap: 5px; height: 38px;">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
            <path
              d="M1.5 1a.5.5 0 0 0-.5.5v4a.5.5 0 0 1-1 0v-4A1.5 1.5 0 0 1 1.5 0h4a.5.5 0 0 1 0 1h-4zM10 .5a.5.5 0 0 1 .5-.5h4A1.5 1.5 0 0 1 16 1.5v4a.5.5 0 0 1-1 0v-4a.5.5 0 0 0-.5-.5h-4a.5.5 0 0 1-.5-.5zM.5 10a.5.5 0 0 1 .5.5v4a.5.5 0 0 0 .5.5h4a.5.5 0 0 1 0 1h-4A1.5 1.5 0 0 1 0 14.5v-4a.5.5 0 0 1 .5-.5zm15 0a.5.5 0 0 1 .5.5v4a1.5 1.5 0 0 1-1.5 1.5h-4a.5.5 0 0 1 0-1h4a.5.5 0 0 0 .5-.5v-4a.5.5 0 0 1 .5-.5z" />
          </svg>
        </button>
      </div>
    </div>


    <!-- Spreadsheet container -->
    <div id="app" style="height: 55vh;"></div>


    <script src="{{ asset('js/modulComponent/script.js') }}"></script>
    <script>
      // Data dari server
      const groupedComponents = @json($groupedComponents ?? []);
      const columns = @json($columns ?? []);
      const componentTypes = @json($componentTypes ?? []);
      const componentOptions = @json($componentOptions ?? []);
      const modul = @json($modul ?? []);
      const modulData = @json($modulData ?? []);
      const recordId = @json($recordId);
      const fieldMapping = @json($fieldMapping ?? []);
      const dataValMap = @json($dataValMap ?? []);
      const dataValidationCol = @json($dataValidationCol ?? []);
      const partComponentsData = @json($partComponentsData ?? []);
      const definedNames = @json($definedNames);
    </script>
    <script src="{{ asset('js/modulComponent/univer.js') }}"></script>
  </div>
