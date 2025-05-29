<div style="width: 100%; overflow-x: scroll;">
  <!-- Stylesheets and scripts -->
  <script src="https://bossanova.uk/jspreadsheet/v5/jspreadsheet.js"></script>
  <script src="https://jsuites.net/v5/jsuites.js"></script>
  <link rel="stylesheet" href="https://bossanova.uk/jspreadsheet/v5/jspreadsheet.css" />
  <link rel="stylesheet" href="https://jsuites.net/v5/jsuites.css" />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Material+Icons" />
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <style>
    .selection .select2-selection {
      height: 38px !important;
    }

    #select2-modulReference-container {
      font-size: 14px !important;
      height: 38px !important;
      line-height: 36px !important;
    }

    .select2-selection__arrow {
      height: 38px !important;

    }

    #select2-modulReference-results {
      padding: 8px !important;
      font-size: 14px !important;
    }
  </style>


  <!-- Tambahkan select option ini di atas atau di bawah elemen spreadsheet -->
  <div class="flex w-full gap-x-6">
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
  </div>


  <div id="spreadsheet" style="width:100%; font-size:14px;"></div>


  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


  <script>
    $(document).ready(function() {
      $('#modulReference').select2({
        placeholder: "--Pilih Modul--",
        allowClear: true,
        width: '100%',
      });
    });
    // Data dari server (ganti sesuai data aslinya)
    const groupedComponents = @json($groupedComponents ?? []);
    const columns = @json($columns ?? []);
    const componentTypes = @json($componentTypes ?? []);
    const componentOptions = @json($componentOptions ?? []);
    const modul = @json($modul ?? []);
    const modulData = @json($modulData ?? []);

    const namaModulIndex = columns.indexOf('nama_modul');
    const componentIndex = columns.indexOf('component');

    console.log('Modul Name:', modul);

    function mapDataToColumns(comp) {
      const fieldMapping = {
        'cat': 'cat',
        'code': 'type',
        'KS': 'kode',
        'name': 'component',
        'material': 'bahan',
        'thickness': 't_bahan',
        'number_of_sub': 'jumlah',
        'V': 'v',
        'V2': 'v2',
        'H': 'h',
        'profile3': 'profile3',
        'profile2': 'profile2',
        'profile': 'profile',
        'outside': 'luar',
        'inside': 'dalam',
        'P1': 'p1',
        'P2': 'p2',
        'L1': 'l1',
        'L2': 'l2',
        'rail': 'rel',
        'hinge': 'engsel',
        'number_of_anodize': 'jumlah_anodize@',
        'minifix': 'minifix',
        'dowel': 'dowel'
      };

      let componentRow = new Array(columns.length).fill('');

      componentRow[componentIndex] = comp.component || comp.name || '';

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

      const pIndex = columns.indexOf('p');
      const lIndex = columns.indexOf('l');
      const ukuranIndex = columns.indexOf('ukuran');
      if (pIndex >= 0 && lIndex >= 0 && ukuranIndex >= 0) {
        const p = comp['P1'] || comp['P2'] || comp['p'];
        const l = comp['L1'] || comp['L2'] || comp['l'];
        if (p && l) {
          componentRow[ukuranIndex] = `${p} x ${l}`;
        }
      }

      return componentRow;
    }

    let data = [];
    let mergeCellsConfig = [];

    Object.entries(groupedComponents).forEach(([modulName, components], modulIndex) => {
      const modulStartRow = data.length;

      let modulRow = new Array(columns.length).fill('');
      modulRow[namaModulIndex] = modulName || '';
      data.push(modulRow);

      let componentCount = 0;

      if (Array.isArray(components)) {
        components.forEach(comp => {
          if (comp && typeof comp === 'object') {
            const componentRow = mapDataToColumns(comp);
            data.push(componentRow);
            componentCount++;
          }
        });
      }

      if (componentCount > 0) {
        mergeCellsConfig.push({
          row: modulStartRow,
          col: namaModulIndex,
          rowspan: componentCount + 1,
          colspan: 1
        });
      }

      if (modulIndex < Object.keys(groupedComponents).length - 1) {
        data.push(new Array(columns.length).fill(''));
      }
    });

    let columnDefs = columns.map((col, index) => {
      let columnDef = {
        title: col,
        width: index === namaModulIndex ? 250 : (index === componentIndex ? 250 : 50),
        readonly: index !== componentIndex
      };

      if (col === 'type') {
        columnDef.type = 'dropdown';
        columnDef.source = componentTypes.map(type => type.value);
        columnDef.afterChange = (changes, source) => {
          if (source === 'edit') {
            const [row, prop, oldValue, newValue] = changes[0];
            if (prop === 'type' && newValue !== oldValue) {
              Livewire.emit('typeChanged', newValue);
            }
          }
        };
      } else if (col === 'component') {
        columnDef.type = 'dropdown';
        columnDef.source = componentOptions.map(comp => comp.value);

        columnDef.renderer = function(instance, td, row, col, prop, value, cellProperties) {
          Handsontable.renderers.TextRenderer.apply(this, arguments);
          return td;
        };

        columnDef.afterChange = (changes, source) => {
          if (source === 'edit') {
            const [row, prop, oldValue, newValue] = changes[0];
            if (prop === 'component' && newValue !== oldValue) {
              const selectedComponent = componentOptions.find(c => c.value === newValue);
              if (selectedComponent && selectedComponent.data) {
                const hot = this;
                Object.entries(selectedComponent.data).forEach(([key, val]) => {
                  const colIndex = columns.indexOf(key);
                  if (colIndex >= 0) {
                    hot.setDataAtCell(row, colIndex, val);
                  }
                });

                // Jika ada data modul, update juga kolom nama_modul
                if (selectedComponent.modul !== undefined && namaModulIndex >= 0) {
                  hot.setDataAtCell(row, namaModulIndex, selectedComponent.modul);
                }
              }
            }
          }
        };
      } else if (col === 'no' || col === 'jml' || col === 'jumlah') {
        columnDef.type = 'numeric';
      }

      return columnDef;
    });

    const spreadsheet = jspreadsheet(document.getElementById('spreadsheet'), {
      toolbar: true,
      worksheets: [{
        name: 'Components',
        data: data,
        columns: columnDefs,
        minDimensions: [columns.length, Math.max(1, data.length)],
        freezeColumns: 7,
        freezeRows: 7,
        mergeCells: mergeCellsConfig,
        allowInsertColumn: false,
        allowDeleteColumn: false,
        tableOverflow: true,
        tableWidth: "100%",
      }],
    });

    if (modul || modulData[modul]) {
      let components = [];
      try {
        components = JSON.parse(modulData[modul]); // Ambil data dari modulData
      } catch (err) {
        console.error('Error parsing modulData:', err);
      }
      const componentIndex = columns.indexOf('component');
      const namaModulIndex = columns.indexOf('nama_modul');
      console.log(namaModulIndex);
      if (componentIndex >= 0) {
        components.forEach((compObj, i) => {
          const rowIndex = i + 1;
          spreadsheet[0].setValueFromCoords(namaModulIndex, 0, modul);

          if (spreadsheet[0].options.data[rowIndex]) {
            spreadsheet[0].setValueFromCoords(componentIndex, rowIndex, compObj.component);
          } else {
            console.warn(`Row index ${rowIndex} tidak ada di spreadsheet. Membuat baris baru.`);
            spreadsheet[0].insertRow([null, null, null]);
            spreadsheet[0].setValueFromCoords(componentIndex, rowIndex, compObj.component);
          }
        });
      } else {
        console.error('Kolom component tidak ditemukan.');
      }
    } else {
      console.warn('Tidak ada data component untuk modul:', modul);
    }



    $('#modulSelect').on('change', function() {
      const selectedValue = $('#modulSelect').val();
      if (!selectedValue) return;

      const namaModulIndex = columns.indexOf('nama_modul');
      const newData = [
        Array(columns.length).fill('').map((val, idx) =>
          idx === namaModulIndex ? selectedValue : val
        ),
        Array(columns.length).fill('')
      ];

      spreadsheet[0].setData(newData);
      console.log(JSON.stringify(spreadsheet[0].getData()));
    });

    $(document).ready(function() {
      $('#modulReference').on('change', function() {
        const modulValue = $(this).val();
        if (!modulValue) return;

        console.log('Modul dipilih:', modulValue);

        $.ajax({
          url: '/get-modul-data',
          method: 'GET',
          data: {
            modul: modulValue
          },
          success: function(response) {
            console.log('Data diterima:', response);

            if (response.success) {
              let components = [];
              try {
                components = JSON.parse(response.components);
              } catch (err) {
                console.error('Gagal parse JSON:', err);
                return;
              }

              const componentIndex = columns.indexOf('component');
              console.log('Component Index:', componentIndex);
              if (componentIndex < 0) {
                console.error('Kolom component tidak ditemukan.');
                return;
              }

              const totalRows = spreadsheet[0].options.data.length;

              for (let row = 1; row < totalRows; row++) {
                spreadsheet[0].setValueFromCoords(componentIndex, row, null);
              }

              // Masukkan data baru
              components.forEach((compObj, i) => {
                const rowIndex = i + 1;

                if (spreadsheet[0].options.data[rowIndex]) {
                  spreadsheet[0].setValueFromCoords(componentIndex, rowIndex, compObj.component);
                } else {
                  spreadsheet[0].insertRow([null, null, null]); // Sesuaikan jumlah kolom
                  spreadsheet[0].setValueFromCoords(componentIndex, rowIndex, compObj.component);
                }
              });

            } else {
              console.warn('Tidak ada data untuk modul:', modulValue);
            }
          },
          error: function(xhr, status, error) {
            console.error('Gagal ambil data:', error);
          }
        });
      });
    });


    $(document).on('click', '#key-bindings-1', function() {
      const spreadsheetData = spreadsheet[0].getData();
      const selectedModul = $('#modulSelect').val();
      const referenceModul = $('#modulReference').val();

      if (!selectedModul) {
        alert('Pilih modul terlebih dahulu!');
        return;
      }

      const payload = {
        modul: selectedModul,
        reference_modul: referenceModul,
        data: spreadsheetData,
        columns: columns
      };

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
            console.log('Data berhasil disimpan:');
          } else {
            alert('Gagal menyimpan data: ' + data.message);
          }
        },
        error: function(xhr, status, error) {
          console.error('Error:', error);
        }
      });
    });
  </script>



</div>
