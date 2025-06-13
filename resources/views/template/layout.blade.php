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

<div style="width: 100%; position: relative;">
  @yield('content')
</div>
