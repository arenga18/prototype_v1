@extends('template.layout')
@section('content')
  <!-- Spreadsheet container -->
  <div id="app" style="height: 70vh;"></div>

  <script src="{{ asset('js/partComponent/script.js') }}"></script>
  <script>
    const partData = @json($partData ?? []);
    const recordId = @json($recordId);
    const dataValMap = @json($dataValMap ?? []);
    const dataValidationCol = @json($dataValidationCol ?? []);
  </script>
  <script src="{{ asset('js/partComponent/univer.js') }}"></script>
@endsection
