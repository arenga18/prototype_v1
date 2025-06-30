<div style="width: 100%; position: relative;">
  <!-- Core React harus di-load pertama -->
  <script src="https://unpkg.com/react@18.2.0/umd/react.production.min.js" crossorigin></script>
  <script src="https://unpkg.com/react-dom@18.2.0/umd/react-dom.production.min.js" crossorigin></script>

  <!-- Dependency lainnya -->
  <script src="https://unpkg.com/rxjs@7.8.1/dist/bundles/rxjs.umd.min.js" crossorigin></script>
  <script src="https://unpkg.com/echarts@5.6.0/dist/echarts.min.js" crossorigin></script>
  <script src="https://unpkg.com/@univerjs/presets/lib/umd/index.js"></script>

  {{-- Script Sheet Core --}}
  <script src="https://unpkg.com/@univerjs/preset-sheets-core/lib/umd/index.js"></script>
  <script src="https://unpkg.com/@univerjs/preset-sheets-core/lib/umd/locales/en-US.js"></script>
  <link rel="stylesheet" href="https://unpkg.com/@univerjs/preset-sheets-core/lib/index.css" />

  {{-- Script Data Validation --}}
  <script src="https://unpkg.com/@univerjs/preset-sheets-data-validation/lib/umd/index.js"></script>
  <script src="https://unpkg.com/@univerjs/preset-sheets-data-validation/lib/umd/locales/en-US.js"></script>
  <link rel="stylesheet" href="https://unpkg.com/@univerjs/preset-sheets-data-validation/lib/index.css" />

  {{-- Script Sheets Filter --}}
  <script src="https://unpkg.com/@univerjs/preset-sheets-filter/lib/umd/index.js"></script>
  <script src="https://unpkg.com/@univerjs/preset-sheets-filter/lib/umd/locales/en-US.js"></script>
  <link rel="stylesheet" href="https://unpkg.com/@univerjs/preset-sheets-filter/lib/index.css" />

  {{-- Script Sheets Sort --}}
  <script src="https://unpkg.com/@univerjs/preset-sheets-sort/lib/umd/index.js"></script>
  <script src="https://unpkg.com/@univerjs/preset-sheets-sort/lib/umd/locales/en-US.js"></script>
  <link rel="stylesheet" href="https://unpkg.com/@univerjs/preset-sheets-sort/lib/index.css" />

  {{-- Script Conditional Formatting --}}
  <script src="https://unpkg.com/@univerjs/preset-sheets-conditional-formatting/lib/umd/index.js"></script>
  <script src="https://unpkg.com/@univerjs/preset-sheets-conditional-formatting/lib/umd/locales/en-US.js"></script>
  <link rel="stylesheet" href="https://unpkg.com/@univerjs/preset-sheets-conditional-formatting/lib/index.css" />

  {{-- Script Find Replace --}}
  <script src="https://unpkg.com/@univerjs/preset-sheets-find-replace/lib/umd/index.js"></script>
  <script src="https://unpkg.com/@univerjs/preset-sheets-find-replace/lib/umd/locales/en-US.js"></script>
  <link rel="stylesheet" href="https://unpkg.com/@univerjs/preset-sheets-find-replace/lib/index.css" />

  <!-- Scripts tambahan -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="{{ asset('css/style.css') }}">

  <div class="mb-4 flex items-center gap-x-3">
    <button id="fullscreen-btn" type="button" class="border py-2 px-3 rounded-1 font-bold text-sm">
      Fullscreen
    </button>
    <div class="flex items-center gap-x-2">
      <div>
        <!-- Button trigger modal -->
        <button type="button" class="bg-primary py-2 px-3 rounded-1 text-white font-bold text-sm"
          data-bs-toggle="modal" data-bs-target="#modulModal">
          Tambah modul
        </button>
        <!-- Modal -->
        <div class="modal fade" id="modulModal" tabindex="1" aria-labelledby="modulModalLabel" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h1 class="modal-title fs-5" id="modulModalLabel">Tambah modul</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <div class="mb-4 w-full">
                  <label for="modulSelect" class="block mb-2 text-sm font-medium text-gray-700">Modul</label>
                  <select id="modulSelect" class="border border-gray-300 text-sm rounded p-2 w-full">
                    @if (count($allModuls['array']) > 1)
                      <option value="">--Pilih Modul--</option>
                    @endif

                    @forelse($allModuls['array'] as $item)
                      @if (isset($item['modul']['nama_modul']))
                        <option value="{{ $item['modul']['nama_modul'] }}"
                          {{ count($allModuls['array']) === 1 ? 'selected' : '' }}>
                          {{ $item['modul']['nama_modul'] }}
                        </option>
                      @endif
                    @empty
                      <option value="">Tidak ada modul tersedia</option>
                    @endforelse
                  </select>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Tambah</button>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div>
        <!-- Button trigger modal -->
        <button type="button" class="bg-primary py-2 px-3 rounded-1 text-white font-bold text-sm"
          data-bs-toggle="modal" data-bs-target="#partModal">
          Tambah Part
        </button>
        <!-- Modal -->
        <div class="modal fade" id="partModal" tabindex="1" aria-labelledby="partModalLabel" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h1 class="modal-title fs-5" id="partModalLabel">Tambah part</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <div class="mb-4 w-full">
                  <label for="partSelect" class="block mb-2 text-sm font-medium text-gray-700">Part</label>
                  <select id="partSelect" class="border border-gray-300 text-sm rounded p-2 w-full">
                    @if (count($allParts['array'] ?? []) > 1)
                      <option value="">--Pilih Modul--</option>
                    @endif

                    @foreach ($allParts['array'] ?? [] as $item)
                      <option value="{{ $item['part']['part_name'] }}"
                        {{ count($allParts['array'] ?? []) === 1 ? 'selected' : '' }}>
                        {{ $item['part']['part_name'] }}
                      </option>
                    @endforeach

                    @if (empty($allParts['array']))
                      <option value="">Tidak ada part tersedia</option>
                    @endif
                  </select>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Tambah</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Spreadsheet container -->
  <div id="app" style="height: 70vh;"></div>
  <script src="{{ asset('js/modulBreakdown/script.js') }}"></script>
  <script>
    const groupedComponents = @json($groupedComponents ?? []);
    const partComponentsData = @json($partComponentsData ?? []);
    const columns = @json($columns ?? []);
    const modul = @json($modul ?? []);
    const modulData = @json($modulData ?? []);
    const recordId = @json($recordId);
    const fieldMapping = @json($fieldMapping ?? []);
    const dataValMap = @json($dataValMap ?? []);
    const dataValidationCol = @json($dataValidationCol ?? []);
    const projectData = @json($allSpecs);
    const definedNames = @json($definedNames);
    const allModuls = @json($allModuls);
    const allParts = @json($allParts);
  </script>
  <script src="{{ asset('js/modulBreakdown/univer.js') }}"></script>

</div>
