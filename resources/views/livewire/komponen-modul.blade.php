<div style="width: 100%; position: relative;">
  <!-- Scripts Univer -->
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

  <style>
    html,
    body,
    #root,
    #app {
      padding: 0;
      margin: 0;
      height: 100%;
    }

    .fullscreen-mode {
      position: fixed !important;
      top: 0 !important;
      left: 0 !important;
      width: 100vw !important;
      height: 90vh !important;
      z-index: 100 !important;
      background: white;
      padding: 20px;
    }

    .fullscreen-mode #app {
      height: calc(90vh - 40px) !important;
    }

    .fullscreen-mode #fullscreen-btn {
      top: 10px !important;
      right: 20px !important;
    }

    .selection .select2-selection {
      height: 38px !important;
    }

    #select2-modulReference-container {
      font-size: 14px !important;
      height: 38px !important;
      line-height: 36px !important;
    }

    .select2-container--default .select2-selection--single {
      border-color: #d1d5db !important;
    }

    .select2-selection__arrow {
      height: 38px !important;

    }

    #select2-modulReference-results {
      padding: 8px !important;
      font-size: 14px !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__clear {
      height: 38px !important;
    }
  </style>

  <!-- Form controls -->
  <div class="flex w-full gap-x-6 items-center">
    <div class="mb-4">
      <label for="modul" class="block mb-2 text-sm font-medium text-gray-700">Modul</label>
      <select id="modulSelect" wire:model="modul" class="border border-gray-300 text-sm rounded p-2">
        <option value="">--Pilih Modul--</option>
        @foreach ($modulList as $m)
          <option value="{{ $m }}">{{ $m }}</option>
        @endforeach
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
    <button id="fullscreen-btn"
      style="z-index: 1000; width: fit-content; background: #fff; border: 1px solid #ddd; border-radius: 4px; padding: 5px 10px; cursor: pointer; display: flex; align-items: center; gap: 5px; height: fit-content;">
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
        <path
          d="M1.5 1a.5.5 0 0 0-.5.5v4a.5.5 0 0 1-1 0v-4A1.5 1.5 0 0 1 1.5 0h4a.5.5 0 0 1 0 1h-4zM10 .5a.5.5 0 0 1 .5-.5h4A1.5 1.5 0 0 1 16 1.5v4a.5.5 0 0 1-1 0v-4a.5.5 0 0 0-.5-.5h-4a.5.5 0 0 1-.5-.5zM.5 10a.5.5 0 0 1 .5.5v4a.5.5 0 0 0 .5.5h4a.5.5 0 0 1 0 1h-4A1.5 1.5 0 0 1 0 14.5v-4a.5.5 0 0 1 .5-.5zm15 0a.5.5 0 0 1 .5.5v4a1.5 1.5 0 0 1-1.5 1.5h-4a.5.5 0 0 1 0-1h4a.5.5 0 0 0 .5-.5v-4a.5.5 0 0 1 .5-.5z" />
      </svg>
      <span>Fullscreen</span>
    </button>
  </div>



  <!-- Spreadsheet container -->
  <div id="app" style="height: 55vh;"></div>

  <script>
    $(document).ready(function() {
      $('#modulReference').select2({
        placeholder: "--Pilih Modul--",
        allowClear: true,
        width: '100%',
      });
    });


    const fullscreenBtn = document.getElementById('fullscreen-btn');
    const container = document.querySelector('div[style="width: 100%; position: relative;"]');

    fullscreenBtn.addEventListener('click', function() {
      container.classList.toggle('fullscreen-mode');

      // Update icon dan teks tombol
      const icon = fullscreenBtn.querySelector('svg');
      const isFullscreen = container.classList.contains('fullscreen-mode');

      if (isFullscreen) {
        fullscreenBtn.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
              <path d="M5.5 0a.5.5 0 0 1 .5.5v4A1.5 1.5 0 0 1 4.5 6h-4a.5.5 0 0 1 0-1h4a.5.5 0 0 0 .5-.5v-4a.5.5 0 0 1 .5-.5zm5 0a.5.5 0 0 1 .5.5v4a.5.5 0 0 0 .5.5h4a.5.5 0 0 1 0 1h-4A1.5 1.5 0 0 1 10 4.5v-4a.5.5 0 0 1 .5-.5zM0 10.5a.5.5 0 0 1 .5-.5h4A1.5 1.5 0 0 1 6 11.5v4a.5.5 0 0 1-1 0v-4a.5.5 0 0 0-.5-.5h-4a.5.5 0 0 1-.5-.5zm10 1a1.5 1.5 0 0 1 1.5-1.5h4a.5.5 0 0 1 0 1h-4a.5.5 0 0 0-.5.5v4a.5.5 0 0 1-1 0v-4z"/>
            </svg>
            Exit Fullscreen
          `;
      } else {
        fullscreenBtn.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
              <path d="M1.5 1a.5.5 0 0 0-.5.5v4a.5.5 0 0 1-1 0v-4A1.5 1.5 0 0 1 1.5 0h4a.5.5 0 0 1 0 1h-4zM10 .5a.5.5 0 0 1 .5-.5h4A1.5 1.5 0 0 1 16 1.5v4a.5.5 0 0 1-1 0v-4a.5.5 0 0 0-.5-.5h-4a.5.5 0 0 1-.5-.5zM.5 10a.5.5 0 0 1 .5.5v4a.5.5 0 0 0 .5.5h4a.5.5 0 0 1 0 1h-4A1.5 1.5 0 0 1 0 14.5v-4a.5.5 0 0 1 .5-.5zm15 0a.5.5 0 0 1 .5.5v4a1.5 1.5 0 0 1-1.5 1.5h-4a.5.5 0 0 1 0-1h4a.5.5 0 0 0 .5-.5v-4a.5.5 0 0 1 .5-.5z"/>
            </svg>
            Fullscreen
          `;
      }

      // Trigger resize event untuk Univer agar menyesuaikan ukuran
      setTimeout(() => {
        window.dispatchEvent(new Event('resize'));
      }, 100);
    });

    // Handle ESC key untuk keluar dari fullscreen
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape' && container.classList.contains('fullscreen-mode')) {
        container.classList.remove('fullscreen-mode');
        fullscreenBtn.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
              <path d="M1.5 1a.5.5 0 0 0-.5.5v4a.5.5 0 0 1-1 0v-4A1.5 1.5 0 0 1 1.5 0h4a.5.5 0 0 1 0 1h-4zM10 .5a.5.5 0 0 1 .5-.5h4A1.5 1.5 0 0 1 16 1.5v4a.5.5 0 0 1-1 0v-4a.5.5 0 0 0-.5-.5h-4a.5.5 0 0 1-.5-.5zM.5 10a.5.5 0 0 1 .5.5v4a.5.5 0 0 0 .5.5h4a.5.5 0 0 1 0 1h-4A1.5 1.5 0 0 1 0 14.5v-4a.5.5 0 0 1 .5-.5zm15 0a.5.5 0 0 1 .5.5v4a1.5 1.5 0 0 1-1.5 1.5h-4a.5.5 0 0 1 0-1h4a.5.5 0 0 0 .5-.5v-4a.5.5 0 0 1 .5-.5z"/>
            </svg>
            Fullscreen
          `;
        window.dispatchEvent(new Event('resize'));
      }
    });


    // Data dari server
    const groupedComponents = @json($groupedComponents ?? []);
    const columns = @json($columns ?? []);
    const componentTypes = @json($componentTypes ?? []);
    const componentOptions = @json($componentOptions ?? []);
    const modul = @json($modul ?? []);
    const modulData = @json($modulData ?? []);
    const recordId = @json($recordId);
    const fieldMapping = @json($fieldMapping ?? []);

    const namaModulIndex = columns.indexOf('nama_modul');
    const componentIndex = columns.indexOf('component');
    const typeIndex = columns.indexOf('type');

    // Inisialisasi Univer
    const {
      createUniver
    } = UniverPresets;
    const {
      LocaleType,
      merge,
      BooleanNumber
    } = UniverCore;
    const {
      defaultTheme
    } = UniverDesign;
    const {
      UniverSheetsCorePreset
    } = UniverPresetSheetsCore;
    const {
      UniverSheetsDataValidationPreset
    } = UniverPresetSheetsDataValidation;

    const {
      univerAPI
    } = createUniver({
      locale: LocaleType.EN_US,
      locales: {
        [LocaleType.EN_US]: merge({},
          UniverPresetSheetsCoreEnUS,
          UniverPresetSheetsDataValidationEnUS
        ),
      },
      theme: defaultTheme,
      presets: [UniverSheetsCorePreset(), UniverSheetsDataValidationPreset()],
    });

    // Siapkan data untuk Univer
    function prepareUniverData() {
      let data = {};

      // Tambahkan header kolom di baris pertama
      data[0] = {};
      columns.forEach((col, index) => {
        data[0][index] = {
          v: col,
          s: {
            bl: 1, // bold
            ht: 2, // horizontal text alignment
            vt: 2, // Vertical text alignment
            fs: 11 // Font size
          }
        };
      });

      // Mulai data dari baris 1 (setelah header)
      let currentRow = 1;

      // Check if we have modul data to load
      if (modul || modulData[modul]) {
        let components = [];
        try {
          components = JSON.parse(modulData[modul]); // Ambil data dari modulData
        } catch (err) {
          console.error('Error parsing modulData:', err);
        }

        // Set nama modul hanya di baris pertama data (baris 1)
        data[currentRow] = {};
        data[currentRow][namaModulIndex] = {
          v: modul
        };
        currentRow++;

        if (componentIndex >= 0 && components.length > 0) {
          components.forEach((compObj) => {
            data[currentRow] = {};

            // Isi component dan field lainnya
            if (compObj.component) {
              data[currentRow][componentIndex] = {
                v: compObj.component
              };
            }

            // Map other fields
            Object.keys(compObj).forEach(key => {
              const colIndex = columns.indexOf(key);
              if (colIndex >= 0) {
                data[currentRow][colIndex] = {
                  v: compObj[key]
                };
              }
            });

            currentRow++;
          });
        } else if (componentIndex < 0) {
          console.error('Kolom component tidak ditemukan.');
        }
      } else {
        console.warn('Tidak ada data component untuk modul:', modul);
      }

      // Add grouped components data if exists
      Object.entries(groupedComponents).forEach(([modulName, components], modulIndex) => {
        // Set nama modul hanya di baris pertama grup
        data[currentRow] = {};
        data[currentRow][namaModulIndex] = {
          v: modulName || ''
        };
        currentRow++;

        if (Array.isArray(components)) {
          components.forEach((comp) => {
            if (comp && typeof comp === 'object') {
              const rowData = mapDataToColumns(comp);
              data[currentRow] = {};
              Object.keys(rowData).forEach(col => {
                data[currentRow][col] = {
                  v: rowData[col]
                };
              });
              currentRow++;
            }
          });
        }

        // Tambahkan baris kosong antara modul
        if (modulIndex < Object.keys(groupedComponents).length - 1) {
          data[currentRow] = {};
          currentRow++;
        }
      });

      return {
        data,
        mergeCells: [] // Return empty array for mergeCells
      };
    }

    // Fungsi untuk memetakan data ke kolom
    function mapDataToColumns(comp) {


      let componentRow = {};

      componentRow[componentIndex] = comp.component || comp.name || '';

      if (typeIndex >= 0) {
        componentRow[typeIndex] = comp.code || '';
      }

      if (namaModulIndex >= 0) {
        componentRow[namaModulIndex] = comp.modul || '';
      }

      Object.entries(fieldMapping).forEach(([sourceField, targetColumn]) => {
        const colIndex = columns.indexOf(targetColumn);
        if (colIndex >= 0 && comp[sourceField] !== undefined && comp[sourceField] !== null) {
          componentRow[colIndex] = comp[sourceField];
        }
      });

      columns.forEach((col, index) => {
        if (index !== componentIndex && index !== namaModulIndex && comp[col] !== undefined && comp[col] !== null) {
          componentRow[index] = comp[col];
        }
      });

      return componentRow;
    }

    // Fungsi untuk membuat definisi kolom
    function createColumnDefinitions() {
      return columns.map(col => {
        return {
          name: col
        };
      });
    }

    // Siapkan data untuk Univer
    const {
      data: cellData,
      mergeCells
    } = prepareUniverData();
    const columnDefs = createColumnDefinitions();

    // Buat workbook
    const workbook = univerAPI.createWorkbook({
      name: "Components Sheet",
      sheetCount: 1,
      sheets: {
        sheet1: {
          id: "sheet1",
          name: "Components",
          tabColor: "#FF0000",
          hidden: BooleanNumber.FALSE,
          freeze: {
            xSplit: 7,
            ySplit: 1,
            startRow: 1,
            startColumn: 7
          },
          zoomRatio: 0.8,
          rowCount: Math.max(2, Object.keys(cellData).length),
          columnCount: columns.length,
          defaultColumnWidth: 30,
          defaultRowHeight: 25,
          mergeData: mergeCells,
          cellData: cellData,
          rowData: [],
          columnData: columnDefs,
          rowHeader: {
            width: 40
          },
          columnHeader: {
            height: 20
          }
        },
      },
    });

    // Dapatkan instance worksheet
    const worksheet = workbook.getActiveSheet();

    columns.forEach((col, index) => {
      if (index === namaModulIndex || index === componentIndex) {
        worksheet.setColumnWidth(index, 200);
      } else if (col === 'proses_khusus') {
        worksheet.setColumnWidth(index, 140);
      } else {
        worksheet.setColumnWidth(index, 40);
      }
    });

    function applyDropdownToColumn(columnIndex, options, clearInvalid = true) {
      if (columnIndex <= 0 || !options?.length) return;

      try {
        const dropdownRule = univerAPI
          .newDataValidation()
          .requireValueInList(options.map(opt => opt.value || opt))
          .setOptions({
            renderMode: univerAPI.Enum.DataValidationRenderMode.ARROW,
            allowInvalid: false,
            showDropDown: true,
            showErrorMessage: true
          })
          .build();

        const range = worksheet.getRange(1, columnIndex, worksheet.getMaxRows(), 1);

        if (clearInvalid) {
          const values = range.getValue() || [];
          const safeValues = Array.isArray(values) ? values : [
            [values]
          ];

          safeValues.forEach((row, i) => {
            const cellValue = Array.isArray(row) ? row[0] : row;
            if (cellValue && !options.some(opt => (opt.value || opt) === cellValue)) {
              worksheet.getRange(i + 1, columnIndex).clearValue();
            }
          });
        }

        range.setDataValidation(dropdownRule);
      } catch (error) {
        console.error(`Error applying dropdown to column ${columnIndex}:`, error);
      }
    }

    const COLUMN_DROPDOWNS = {
      type: {
        index: 1,
        options: componentOptions
      },
      component: {
        index: 6,
        options: componentOptions
      },
      luar: {
        index: 18,
        options: componentOptions
      },
      dalam: {
        index: 20,
        options: componentOptions
      },
      rel: {
        index: 36,
        options: componentOptions
      },
      engsel: {
        index: 37,
        options: componentOptions
      },
      v: {
        index: 38,
        options: componentOptions
      },
      v2: {
        index: 39,
        options: componentOptions
      },
      h: {
        index: 40,
        options: componentOptions
      },
      nama_barang: {
        index: 41,
        options: componentOptions
      },
    };

    function applyAllDropdowns() {
      Object.values(COLUMN_DROPDOWNS).forEach(({
        index,
        options
      }) => {
        applyDropdownToColumn(index, options);
      });
    }

    applyAllDropdowns()

    // Fungsi untuk mendapatkan semua data dari worksheet
    function getAllData() {
      const workbook = univerAPI.getActiveWorkbook();
      const worksheet = workbook.getActiveSheet();
      const range = worksheet.getRange(0, 0, worksheet.getMaxRows(), worksheet.getMaxColumns());

      const cellDatas = range.getCellDatas();
      const formulas = range.getFormulas();

      const result = [];

      cellDatas.forEach((row, rowIndex) => {
        const rowData = {};
        row.forEach((cell, colIndex) => {
          // Jika ada formula, simpan formula aslinya
          if (formulas[rowIndex][colIndex]) {
            rowData[colIndex] = formulas[rowIndex][colIndex];
          }
          // Jika tidak ada formula, simpan nilai biasa
          else if (cell?.v !== undefined) {
            rowData[colIndex] = cell.v || '';
          } else {
            rowData[colIndex] = '';
          }
        });
        result.push(rowData);
      });

      return result;
    }
    // Event handler untuk perubahan dropdown modul
    $('#modulSelect').on('change', function() {
      const selectedValue = $(this).val();
      if (!selectedValue) return;

      // Dapatkan workbook aktif
      const workbook = univerAPI.getActiveWorkbook();
      const sheet = workbook.getActiveSheet();

      // Kosongkan semua data kecuali header (baris 0)
      for (let row = 1; row < sheet.getMaxRows(); row++) {
        for (let col = 0; col < sheet.getMaxColumns(); col++) {
          const range = sheet.getRange(row, col);
          if (range) {
            range.clear();
          }
        }
      }

      // Set nilai modul di baris 1 (setelah header)
      const modulRange = sheet.getRange(1, namaModulIndex);
      if (modulRange) {
        modulRange.setValue(selectedValue);
      }

      // Jika perlu menambahkan baris kosong di bawahnya
      if (sheet.getMaxRows() < 2) {
        sheet.insertRows(1, 1);
      }
      applyAllDropdowns()
    });

    $('#modulReference').on('change', async function() {
      const modulValue = $(this).val();
      if (!modulValue) return;

      try {
        const response = await $.ajax({
          url: '/get-modul-data',
          method: 'GET',
          data: {
            modul: modulValue
          }
        });

        if (!response.success) {
          console.warn('Tidak ada data untuk modul:', modulValue);
          return;
        }

        let components = [];
        try {
          components = JSON.parse(response.components);
          if (!Array.isArray(components)) {
            throw new Error('Data komponen bukan array');
          }
        } catch (err) {
          console.error('Gagal parse JSON:', err);
          return;
        }

        const workbook = univerAPI.getActiveWorkbook();
        const sheet = workbook.getActiveSheet();
        const componentIndex = columns.indexOf('component');

        if (componentIndex < 0) {
          console.error('Kolom component tidak ditemukan.');
          return;
        }

        // Kosongkan sheet (kecuali header)
        sheet.getRange(2, 1, sheet.getMaxRows() - 1, sheet.getMaxColumns()).clear();

        // Gunakan for...of untuk sequential execution
        for (const [i, compObj] of components.entries()) {
          const rowIndex = i + 2;

          // Tambahkan baris jika diperlukan
          if (rowIndex >= sheet.getMaxRows()) {
            sheet.insertRows(rowIndex, 1);
          }

          // Proses semua kolom secara synchronous
          for (const key of Object.keys(compObj)) {
            const colIndex = columns.indexOf(key);
            if (colIndex >= 0) {
              const value = compObj[key];
              if (value != null) {
                await sheet.getRange(rowIndex, colIndex).setValue(value);
              }
            }
          }

          // Isi component column
          const componentValue = compObj.component || compObj.name || '';
          await sheet.getRange(rowIndex, componentIndex).setValue(componentValue);
        }

        // Terapkan dropdown setelah semua data terisi
        await applyAllDropdowns();

      } catch (error) {
        console.error('Error:', error);
      }
    });

    // Event handler untuk tombol simpan
    $(document).on('click', '#key-bindings-1', function() {
      const spreadsheetData = getAllData();
      const selectedModul = $('#modulSelect').val();
      const referenceModul = $('#modulReference').val();

      if (!selectedModul) {
        alert('Pilih modul terlebih dahulu!');
        return;
      }

      // Rekonstruksi data dengan format yang benar
      const processedData = [];
      let currentModul = selectedModul;

      // Mulai dari baris 1 (setelah header)
      for (let i = 1; i < spreadsheetData.length; i++) {
        const row = spreadsheetData[i];

        // Skip baris kosong
        if (Object.values(row).every(val => val === '')) continue;

        // Jika baris berisi nama modul
        if (row[namaModulIndex] && row[namaModulIndex] !== '') {
          currentModul = row[namaModulIndex];
          continue;
        }

        // Proses baris komponen
        const componentData = {};
        columns.forEach((col, colIndex) => {
          if (row[colIndex] !== undefined && row[colIndex] !== '') {
            componentData[col] = row[colIndex];
          }
        });

        if (Object.keys(componentData).length > 0) {
          processedData.push({
            modul: currentModul,
            data: componentData
          });
        }
      }

      const payload = {
        modul: selectedModul,
        reference_modul: referenceModul,
        components: processedData,
        columns: columns
      };

      console.log('Payload untuk simpan:', payload);

      $.ajax({
        url: '/save-spreadsheet',
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        contentType: 'application/json',
        data: JSON.stringify(payload),
        success: function(data) {
          if (data.status === 'success') {
            alert('Data berhasil disimpan!');
          } else {
            alert('Gagal menyimpan data: ' + data.message);
          }
        },
        error: function(xhr, status, error) {
          alert('Error: ' + error);
        }
      });
    });

    // Event handler untuk tombol update
    $(document).on('click', '#key-bindings-2', function() {
      const spreadsheetData = getAllData();
      console.log('Spreadsheet Data:', spreadsheetData);
      const selectedModul = $('#modulSelect').val();
      const referenceModul = $('#modulReference').val();

      if (!selectedModul) {
        alert('Pilih modul terlebih dahulu!');
        return;
      }

      // Rekonstruksi data dengan format yang benar
      const processedData = [];
      let currentModul = selectedModul;

      for (let i = 1; i < spreadsheetData.length; i++) {
        const row = spreadsheetData[i];

        // Skip baris kosong
        if (Object.values(row).every(val => val === '')) continue;

        // Jika baris berisi nama modul
        if (row[namaModulIndex] && row[namaModulIndex] !== '') {
          currentModul = row[namaModulIndex];
          continue;
        }

        // Proses baris komponen
        const componentData = {};
        columns.forEach((col, colIndex) => {
          if (row[colIndex] !== undefined && row[colIndex] !== '') {
            componentData[col] = row[colIndex];
          }
        });

        if (Object.keys(componentData).length > 0) {
          processedData.push({
            modul: currentModul,
            data: componentData
          });
        }
      }

      console.log('Processed Data:', processedData);

      const payload = {
        modul: selectedModul,
        reference_modul: referenceModul,
        components: processedData,
        columns: columns,
        recordId: recordId
      };

      $.ajax({
        url: '/update-spreadsheet',
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
          'Content-Type': 'application/json'
        },
        data: JSON.stringify(payload),
        success: function(response) {
          if (response.status === 'success') {
            console.log("Data berhasil diperbarui:", response.data);
          } else {
            alert('Error: ' + response.message);
          }
        },
        error: function(xhr) {
          let errorMsg = 'Terjadi kesalahan';
          if (xhr.responseJSON && xhr.responseJSON.message) {
            errorMsg = xhr.responseJSON.message;
          }
          alert(errorMsg);
        }
      });
    });
  </script>
</div>
