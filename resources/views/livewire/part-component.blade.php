<div style="width: 100%; position: relative;">
  <link rel="stylesheet" href="https://unpkg.com/@univerjs/preset-sheets-core/lib/index.css" />
  <link rel="stylesheet" href="https://unpkg.com/@univerjs/preset-sheets-data-validation/lib/index.css" />
  <link rel="stylesheet" href="https://unpkg.com/@univerjs/presets/lib/styles/preset-sheets-find-replace.css" />
  <link rel="stylesheet" href="https://unpkg.com/@univerjs/presets/lib/styles/preset-sheets-conditional-formatting.css" />
  <link rel="stylesheet" href="https://unpkg.com/@univerjs/presets/lib/styles/preset-sheets-sort.css" />
  <link rel="stylesheet" href="https://unpkg.com/@univerjs/presets/lib/styles/preset-sheets-filter.css" />
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.css" rel="stylesheet" />
  <link rel="stylesheet" href={{ asset('css/style.css') }}>

  <div class="mb-4">
    <button id="fullscreen-btn" type="button" class="border py-2 px-3 rounded-1 font-bold text-sm">
      Fullscreen
    </button>
  </div>
  <!-- Spreadsheet container -->
  <div id="app" style="height: 70vh;"></div>


  <script src="https://unpkg.com/react@18.3.1/umd/react.production.min.js"></script>
  <script src="https://unpkg.com/react-dom@18.3.1/umd/react-dom.production.min.js"></script>
  <script src="https://unpkg.com/rxjs/dist/bundles/rxjs.umd.min.js"></script>
  <script src="https://unpkg.com/echarts@5.6.0/dist/echarts.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@univerjs/data-validation@latest/dist/univer-data-validation.umd.js"></script>
  <script src="https://unpkg.com/@univerjs/presets/lib/umd/index.js"></script>
  <script src="https://unpkg.com/@univerjs/preset-sheets-core/lib/umd/index.js"></script>
  <script src="https://unpkg.com/@univerjs/preset-sheets-core/lib/umd/locales/en-US.js"></script>
  <script src="https://unpkg.com/@univerjs/preset-sheets-data-validation/lib/umd/index.js"></script>
  <script src="https://unpkg.com/@univerjs/preset-sheets-data-validation/lib/umd/locales/en-US.js"></script>
  <script src="https://unpkg.com/@univerjs/preset-sheets-find-replace/lib/umd/index.js"></script>
  <script src="https://unpkg.com/@univerjs/preset-sheets-find-replace/lib/umd/locales/en-US.js"></script>
  <script src="https://unpkg.com/@univerjs/preset-sheets-conditional-formatting/lib/umd/index.js"></script>
  <script src="https://unpkg.com/@univerjs/preset-sheets-conditional-formatting/lib/umd/locales/en-US.js"></script>
  <script src="https://unpkg.com/@univerjs/preset-sheets-sort/lib/umd/index.js"></script>
  <script src="https://unpkg.com/@univerjs/preset-sheets-sort/lib/umd/locales/en-US.js"></script>
  <script src="https://unpkg.com/@univerjs/preset-sheets-filter/lib/umd/index.js"></script>
  <script src="https://unpkg.com/@univerjs/preset-sheets-filter/lib/umd/locales/en-US.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
  <script src="{{ asset('js/partComponent/script.js') }}"></script>
  <script>
    const partData = @json($partData ?? []);
    const recordId = @json($recordId);
    const dataValMap = @json($dataValMap ?? []);
    const dataValidationCol = @json($dataValidationCol ?? []);
    const definedNames = @json($definedNames);
  </script>
  <script src="{{ asset('js/partComponent/univer.js') }}"></script>
</div>
