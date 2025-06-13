@extends('template.layout')
@section('content')
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

  <!-- Spreadsheet container -->
  <div id="app" style="height: 55vh;"></div>

  <script src="{{ asset('js/modulBreakdown/script.js') }}"></script>
  <script>
    const groupedComponents = @json($groupedComponents ?? []);
    const partComponentsData = @json($partComponentsData ?? []);
    const columns = @json($columns ?? []);
    const componentTypes = @json($componentTypes ?? []);
    const componentOptions = @json($componentOptions ?? []);
    const modul = @json($modul ?? []);
    const modulData = @json($modulData ?? []);
    const recordId = @json($recordId);
    const fieldMapping = @json($fieldMapping ?? []);
    const dataValMap = @json($dataValMap ?? []);
    const dataValidationCol = @json($dataValidationCol ?? []);
  </script>
  <script src="{{ asset('js/modulBreakdown/univer.js') }}"></script>
@endsection
