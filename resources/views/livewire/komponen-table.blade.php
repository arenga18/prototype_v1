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
  @vite('resources/css/app.css')

  <div class="mb-4 flex items-center gap-x-3">
    <button id="fullscreen-btn" type="button" class="border py-2 px-3 rounded-1 font-bold text-sm">
      Fullscreen
    </button>
    <div class="flex items-center gap-x-2">
      <div class="add-modul w-1/3">
        <!-- Modal toggle -->
        <button data-modal-target="modul-modal" data-modal-toggle="modul-modal"
          class="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded text-xs px-3 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800"
          type="button">
          Tambah Modul
        </button>

        <!-- Main modal -->
        <div id="modul-modal" tabindex="-1" aria-hidden="true"
          class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
          <div class="relative p-4 w-full max-w-2xl max-h-full">
            <!-- Modal content -->
            <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700">
              <!-- Modal header -->
              <div
                class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600 border-gray-200">
                <h3 class="text-xl font-semibold text-gray-900">
                  Tambah Sub Modul
                </h3>
                <button type="button"
                  class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white"
                  data-modal-hide="modul-modal">
                  <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 14 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                  </svg>
                  <span class="sr-only">Close modal</span>
                </button>
              </div>
              <!-- Modal body -->
              <div class="p-4 md:p-5 space-y-4">
                <div class="w-full">
                  <div class="modul-select mb-4">
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
                  <div class="modul-placement">
                    <label for="modul-placement" class="block mb-2 text-sm font-medium text-gray-700">Ditambah di
                      modul:</label>
                    <select id="modul-placement" class="border border-gray-300 text-sm rounded p-2 w-full">
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
              </div>
              <!-- Modal footer -->
              <div class="flex items-center p-4 md:p-5 border-t border-gray-200 rounded-b dark:border-gray-600">
                <button data-modal-hide="modul-modal" type="button"
                  class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Tambah</button>
                <button data-modal-hide="modul-modal" type="button"
                  class="py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">Cancel</button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="add-part w-1/3">
        <!-- Modal toggle -->
        <button data-modal-target="part-modal" data-modal-toggle="part-modal"
          class="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded text-xs px-3 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800"
          type="button">
          Tambah Part
        </button>

        <!-- Main modal -->
        <div id="part-modal" tabindex="-1" aria-hidden="true"
          class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
          <div class="relative p-4 w-full max-w-2xl max-h-full">
            <!-- Modal content -->
            <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700">
              <!-- Modal header -->
              <div
                class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600 border-gray-200">
                <h3 class="text-xl font-semibold text-gray-900">
                  Tambah Part
                </h3>
                <button type="button"
                  class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white"
                  data-modal-hide="part-modal">
                  <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 14 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                  </svg>
                  <span class="sr-only">Close modal</span>
                </button>
              </div>
              <!-- Modal body -->
              <div class="p-4 md:p-5 space-y-4">
                <div class="mb-4 w-full">
                  <label for="partSelect" class="block mb-2 text-sm font-medium text-gray-700">Part</label>
                  <select id="partSelect" class="border border-gray-300 text-sm rounded p-2 w-full">
                    @if (count($allParts['array'] ?? []) > 1)
                      <option value="">--Pilih Part--</option>
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
              <!-- Modal footer -->
              <div class="flex items-center p-4 md:p-5 border-t border-gray-200 rounded-b dark:border-gray-600">
                <button data-modal-hide="part-modal" type="button"
                  class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Tambah</button>
                <button data-modal-hide="part-modal" type="button"
                  class="py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">Cancel</button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="update-modul-codification w-1/3">
        <!-- Modal toggle -->
        <button data-modal-target="kodifikasi-modal" data-modal-toggle="kodifikasi-modal"
          class="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded text-xs px-3 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800"
          type="button">
          Update Kodifikasi Modul
        </button>
        <!-- Main modal -->
        <div id="kodifikasi-modal" tabindex="-1" aria-hidden="true"
          class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
          <div class="relative p-4 w-full max-w-2xl max-h-full">
            <!-- Modal content -->
            <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700">
              <!-- Modal header -->
              <div
                class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600 border-gray-200">
                <h3 class="text-xl font-semibold text-gray-900">
                  Update Kodifikasi Modul
                </h3>
                <button type="button"
                  class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white"
                  data-modal-hide="kodifikasi-modal">
                  <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 14 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                  </svg>
                  <span class="sr-only">Close modal</span>
                </button>
              </div>

              <!-- Modal body -->
              <div class="p-4 md:p-5 space-y-4">
                <div class="mb-4 w-full ">
                  <div class="m-4">
                    <label for="codeCabinetSelect" class="block mb-2 text-sm font-medium text-gray-700">Modul</label>
                    <select id="codeCabinetSelect" class="border border-gray-300 text-sm rounded p-2 w-full">
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
                    <hr>
                  </div>
                </div>

                <!-- Add this section to display the cabinet code -->
                <div class="mb-4 w-full">
                  <div class="form-container">
                    <form id="dynamicForm">
                      <div class="form-group m-4">
                        <label class="block mb-2 text-sm font-medium text-gray-700" for="cabinetCodeDisplay">kode
                          kabinet</label>
                        <input type="text" id="cabinetCodeDisplay"
                          class="border border-gray-300 text-sm rounded p-2 w-full" readonly>
                      </div>

                      <div class="form-group m-4">
                        <label class="block mb-2 text-sm font-medium text-gray-700" for="description_unit">Deskripsi
                          Unit</label>
                        <select id="description_unit" name="description_unit"
                          class="model-select border border-gray-300 text-sm rounded p-2 w-full"
                          data-model="DescriptionUnit">
                          <option value="">Loading...</option>
                        </select>
                      </div>

                      <div class="form-group m-4">
                        <label class="block mb-2 text-sm font-medium text-gray-700" for="box_carcase_shape">Bentuk
                          Box/Carcase</label>
                        <select id="box_carcase_shape" name="box_carcase_shape"
                          class="model-select border border-gray-300 text-sm rounded p-2 w-full"
                          data-model="BoxCarcaseShape">
                          <option value="">Loading...</option>
                        </select>
                      </div>

                      <div class="form-group m-4">
                        <label class="block mb-2 text-sm font-medium text-gray-700" for="finishing">Finishing</label>
                        <select id="finishing" name="finishing"
                          class="model-select border border-gray-300 text-sm rounded p-2 w-full"
                          data-model="Finishing">
                          <option value="">Loading...</option>
                        </select>
                      </div>

                      <div class="form-group m-4">
                        <label class="block mb-2 text-sm font-medium text-gray-700" for="layer_position">Posisi
                          Lapisan</label>
                        <select id="layer_position" name="layer_position"
                          class="model-select border border-gray-300 text-sm rounded p-2 w-full"
                          data-model="LayerPosition">
                          <option value="">Loading...</option>
                        </select>
                      </div>

                      <div class="form-group m-4">
                        <label class="block mb-2 text-sm font-medium text-gray-700" for="box_carcase_content">Isi
                          Box/Carcase</label>
                        <select id="box_carcase_content" name="box_carcase_content"
                          class="model-select border border-gray-300 text-sm rounded p-2 w-full"
                          data-model="BoxCarcaseContent">
                          <option value="">Loading...</option>
                        </select>
                      </div>

                      <div class="form-group m-4">
                        <label class="block mb-2 text-sm font-medium text-gray-700" for="closing_system">Sistem
                          Tutup</label>
                        <select id="closing_system" name="closing_system"
                          class="model-select border border-gray-300 text-sm rounded p-2 w-full"
                          data-model="ClosingSystem">
                          <option value="">Loading...</option>
                        </select>
                      </div>

                      <div class="form-group m-4">
                        <label class="block mb-2 text-sm font-medium text-gray-700" for="number_of_closures">Jumlah
                          Tutup</label>
                        <select id="number_of_closures" name="number_of_closures"
                          class="model-select border border-gray-300 text-sm rounded p-2 w-full"
                          data-model="NumberOfClosure">
                          <option value="">Loading...</option>
                        </select>
                      </div>

                      <div class="form-group m-4">
                        <label class="block mb-2 text-sm font-medium text-gray-700" for="type_of_closure">Jenis
                          Tutup</label>
                        <select id="type_of_closure" name="type_of_closure"
                          class="model-select border border-gray-300 text-sm rounded p-2 w-full"
                          data-model="TypeOfClosure">
                          <option value="">Loading...</option>
                        </select>
                      </div>

                      <div class="form-group m-4">
                        <label class="block mb-2 text-sm font-medium text-gray-700" for="handle">Handle</label>
                        <select id="handle" name="handle"
                          class="model-select border border-gray-300 text-sm rounded p-2 w-full" data-model="Handle">
                          <option value="">Loading...</option>
                        </select>
                      </div>

                      <div class="form-group m-4">
                        <label class="block mb-2 text-sm font-medium text-gray-700" for="acc">Accessories</label>
                        <select id="acc" name="acc"
                          class="model-select border border-gray-300 text-sm rounded p-2 w-full"
                          data-model="Accessories">
                          <option value="">Loading...</option>
                        </select>
                      </div>

                      <div class="form-group m-4">
                        <label class="block mb-2 text-sm font-medium text-gray-700" for="lamp">Lampu</label>
                        <select id="lamp" name="lamp"
                          class="model-select border border-gray-300 text-sm rounded p-2 w-full" data-model="Lamp">
                          <option value="">Loading...</option>
                        </select>
                      </div>

                      <div class="form-group m-4">
                        <label class="block mb-2 text-sm font-medium text-gray-700" for="plinth">Plinth</label>
                        <select id="plinth" name="plinth"
                          class="model-select border border-gray-300 text-sm rounded p-2 w-full" data-model="Plinth">
                          <option value="">Loading...</option>
                        </select>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
              <!-- Modal footer -->
              <div class="flex items-center p-4 md:p-5 border-t border-gray-200 rounded-b dark:border-gray-600">
                <button data-modal-hide="kodifikasi-modal" type="button"
                  class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Perbarui</button>
                <button data-modal-hide="kodifikasi-modal" type="button"
                  class="py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">Cancel</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
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
  <script src="{{ asset('js/modulBreakdown/script.js') }}"></script>
  <script>
    const groupedComponents = @json($groupedComponents ?? []);
    const partComponentsData = @json($partComponentsData ?? []);
    const columns = @json($columns ?? []);
    const modul = @json($modul ?? []);
    const modulData = @json($modulData ?? []);
    const recordId = @json($recordId);
    const nip = @json($nip ?? '');
    const fieldMapping = @json($fieldMapping ?? []);
    const dataValMap = @json($dataValMap ?? []);
    const dataValidationCol = @json($dataValidationCol ?? []);
    const projectData = @json($allSpecs);
    const definedNames = @json($definedNames);
    const allModuls = @json($allModuls);
    const allParts = @json($allParts);
  </script>
  <script src="{{ asset('js/modulBreakdown/univer.js') }}"></script>
  <script src="{{ asset('js/modulBreakdown/updateModul.js') }}"></script>
</div>
