<div style="width: 100%;">
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

  <!-- Spreadsheet container -->
  <div id="app" style="height: 55vh;"></div>

  <script>
    $(document).ready(function() {
      // Data dari server
      const groupedComponents = @json($groupedComponents ?? []);
      const columns = @json($columns ?? []);
      const componentTypes = @json($componentTypes ?? []);
      const componentOptions = @json($componentOptions ?? []);
      const modul = @json($modul ?? []);
      const modulData = @json($modulData ?? []);

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

      // Fungsi untuk memetakan data ke kolom
      function mapDataToColumns(comp) {
        const fieldMapping = {
          'cat': 'cat',
          'code': 'type',
          'KS': 'kode',
          'name': 'component',
          'material': 'bahan',
          'thickness': 't_bahan',
          'number_of_sub': 'sub',
          'V': 'v',
          'V2': 'v2',
          'H': 'h',
          'profile3': 'profile3',
          'profile2': 'profile2',
          'profile': 'profile',
          'outside': 'l_bahan',
          'inside': 'd_bahan',
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

        let componentRow = {};

        componentRow[componentIndex] = comp.component || comp.name || '';

        if (typeIndex >= 0) {
          componentRow[typeIndex] = comp.code || '';
        }

        Object.entries(fieldMapping).forEach(([sourceField, targetColumn]) => {
          const colIndex = columns.indexOf(targetColumn);
          if (colIndex >= 0 && comp[sourceField] !== undefined && comp[sourceField] !== null) {
            componentRow[colIndex] = comp[sourceField];
          }
        });

        columns.forEach((col, index) => {
          if (index !== componentIndex && comp[col] !== undefined && comp[col] !== null) {
            componentRow[index] = comp[col];
          }
        });

        return componentRow;
      }

      // Siapkan data untuk Univer
      function prepareUniverData() {
        let data = {};
        const modulStartRows = {}; // Untuk menyimpan posisi awal setiap modul

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

        let currentRow = 1; // Mulai data dari baris 1 (setelah header)

        // Fungsi untuk menyesuaikan formula berdasarkan modul
        const adjustFormula = (formula, modulStartRow) => {
          return formula.replace(/([A-Z]+)(\d+)/g, (match, col, rowNum) => {
            const newRow = modulStartRow + parseInt(rowNum) - 2;
            return `${col}${newRow}`;
          });
        };

        // Style untuk baris modul (seluruh kolom kuning)
        const modulRowStyle = {
          bg: {
            rgb: '#faf59b' // Kuning
          },
          bl: 1, // Bold
          bd: {
            // Top border
            t: {
              s: 1,
            },
            // Bottom border
            b: {
              s: 1,
            },
            // Left border
            l: {
              s: 1,
            },
            // Right border
            r: {
              s: 1,
            },
          }
        };

        // Process main modul
        if (modul || modulData[modul]) {
          modulStartRows[modul] = currentRow + 1; // Baris setelah nama modul

          let components = [];
          try {
            components = JSON.parse(modulData[modul]);
          } catch (err) {
            console.error('Error parsing modulData:', err);
          }

          // Set seluruh baris nama modul berwarna kuning
          data[currentRow] = {};
          columns.forEach((_, colIndex) => {
            data[currentRow][colIndex] = {
              v: colIndex === namaModulIndex ? modul : '', // Isi hanya di kolom nama modul
              // s: modulRowStyle
            };
          });
          currentRow++;

          if (componentIndex >= 0 && components.length > 0) {
            components.forEach((compObj) => {
              data[currentRow] = {};

              if (compObj.component) {
                data[currentRow][componentIndex] = {
                  v: compObj.component
                };
              }

              Object.keys(compObj).forEach(key => {
                const colIndex = columns.indexOf(key);
                if (colIndex >= 0) {
                  if (typeof compObj[key] === 'string' && compObj[key].startsWith('=')) {
                    data[currentRow][colIndex] = {
                      f: adjustFormula(compObj[key], modulStartRows[modul]),
                      v: ''
                    };
                  } else {
                    data[currentRow][colIndex] = {
                      v: compObj[key]
                    };
                  }
                }
              });
              currentRow++;
            });
          }
        }

        // Process grouped components
        Object.entries(groupedComponents).forEach(([modulName, components], modulIndex) => {
          modulStartRows[modulName] = currentRow + 1; // Baris setelah nama modul

          // Set seluruh baris nama modul berwarna kuning
          data[currentRow] = {};
          columns.forEach((_, colIndex) => {
            data[currentRow][colIndex] = {
              v: colIndex === namaModulIndex ? modulName : '', // Isi hanya di kolom nama modul
              s: modulRowStyle
            };
          });
          currentRow++;

          if (Array.isArray(components)) {
            components.forEach((comp) => {
              if (comp && typeof comp === 'object') {
                const rowData = mapDataToColumns(comp);
                data[currentRow] = {};
                Object.keys(rowData).forEach(col => {
                  if (typeof rowData[col] === 'string' && rowData[col].startsWith('=')) {
                    data[currentRow][col] = {
                      f: adjustFormula(rowData[col], modulStartRows[modulName]),
                      v: ''
                    };
                  } else {
                    data[currentRow][col] = {
                      v: rowData[col]
                    };
                  }
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
          mergeCells: []
        };
      }



      // Buat workbook
      const {
        data: cellData,
        mergeCells
      } = prepareUniverData();
      const workbook = univerAPI.createWorkbook({
        name: "Components Sheet",
        sheetCount: 1,
        sheets: {
          sheet1: {
            id: "sheet1",
            name: "Components",
            tabColor: "#FF0000",
            zoomRatio: 0.8,
            hidden: BooleanNumber.FALSE,
            freeze: {
              xSplit: 7,
              ySplit: 1,
              startRow: 1,
              startColumn: 7
            },
            rowCount: Math.max(10, Object.keys(cellData).length),
            columnCount: columns.length,
            defaultColumnWidth: 30,
            defaultRowHeight: 25,
            mergeData: mergeCells,
            cellData: cellData,
            rowData: [],
            columnData: columns.map(col => ({
              name: col
            })),
            rowHeader: {
              width: 40
            },
            columnHeader: {
              height: 20
            }
          },
        },
      });

      const worksheet = workbook.getActiveSheet();

      // Atur lebar kolom
      columns.forEach((col, index) => {
        if (index === namaModulIndex || index === componentIndex) {
          worksheet.setColumnWidth(index, 200);
        } else {
          worksheet.setColumnWidth(index, 40);
        }
      });

      // Fungsi untuk dropdown component
      function applyComponentDropdown() {
        if (componentIndex >= 0 && componentOptions.length > 0) {
          const componentDropdownRule = univerAPI
            .newDataValidation()
            .requireValueInList(componentOptions.map(option => option.value))
            .setOptions({
              renderMode: univerAPI.Enum.DataValidationRenderMode.ARROW,
              allowInvalid: true,
              showDropDown: true,
              showErrorMessage: false
            })
            .build();

          worksheet.getRange(1, componentIndex, worksheet.getMaxRows(), 1)
            .setDataValidation(componentDropdownRule);
        }
      }

      // Fungsi untuk dropdown type
      function applyTypeDropdown() {
        if (typeIndex >= 0 && componentTypes.length > 0) {
          const typeDropdownRule = univerAPI
            .newDataValidation()
            .requireValueInList(componentTypes.map(type => type.value))
            .setOptions({
              renderMode: univerAPI.Enum.DataValidationRenderMode.ARROW,
              allowInvalid: true,
              showDropDown: true,
              showErrorMessage: false
            })
            .build();

          worksheet.getRange(1, typeIndex, worksheet.getMaxRows(), 1)
            .setDataValidation(typeDropdownRule);
        }
      }

      // Terapkan dropdown
      applyComponentDropdown();
      applyTypeDropdown();

      // Fungsi untuk mendapatkan semua data
      function getAllData() {
        const data = [];
        const rowCount = worksheet.getMaxRows();
        const colCount = worksheet.getMaxColumns();
        const sheetSnapshot = worksheet.getSheet().getSnapshot();
        const cellData = sheetSnapshot.cellData || {};

        for (let row = 0; row < rowCount; row++) {
          const rowData = {};
          for (let col = 0; col < colCount; col++) {
            if (cellData[row] && cellData[row][col]) {
              rowData[col] = cellData[row][col].v || '';
            } else {
              rowData[col] = '';
            }
          }
          data.push(rowData);
        }

        return data;
      }

      // Fungsi simpan data
      function saveSheet() {
        const sheetsData = getAllData();
        const $btn = $('#save-button');
        const originalText = $btn.text();

        $btn.text('Menyimpan...').prop('disabled', true);

        // Rekonstruksi data komponen
        const updatedComponents = {};
        let currentModul = '';
        let componentsForCurrentModul = [];

        sheetsData.forEach(row => {
          if (row[namaModulIndex]) {
            if (currentModul && componentsForCurrentModul.length > 0) {
              updatedComponents[currentModul] = componentsForCurrentModul;
            }
            currentModul = row[namaModulIndex];
            componentsForCurrentModul = [];
          } else if (row[componentIndex] && currentModul) {
            let componentObj = {};
            columns.forEach((col, colIndex) => {
              if (row[colIndex]) {
                componentObj[col] = row[colIndex];
              }
            });
            componentsForCurrentModul.push(componentObj);
          }
        });

        if (currentModul && componentsForCurrentModul.length > 0) {
          updatedComponents[currentModul] = componentsForCurrentModul;
        }

        // Kirim data ke server
        $.ajax({
          url: '/save-univer',
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          contentType: 'application/json',
          data: JSON.stringify({
            modul_id: modul,
            name: 'Sheet Komponen',
            data: JSON.stringify(sheetsData),
            components: updatedComponents
          }),
          success: function(response) {
            if (response.success) {
              alert('Berhasil disimpan!');
            } else {
              alert('Gagal menyimpan: ' + (response.message || 'Terjadi kesalahan'));
            }
          },
          error: function(xhr) {
            alert('Error: ' + (xhr.responseJSON?.message || 'Terjadi kesalahan saat menyimpan'));
          },
          complete: function() {
            $btn.text(originalText).prop('disabled', false);
          }
        });
      }

      // Event handler dengan jQuery
      $('#save-button').on('click', saveSheet);

      // Handler untuk perubahan dropdown modul reference
      $('#modulReference').on('change', function() {
        const modulValue = $(this).val();
        if (!modulValue) return;

        $.ajax({
          url: '/get-modul-data',
          method: 'GET',
          data: {
            modul: modulValue
          },
          success: function(response) {
            if (response.success) {
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

              // Kosongkan data yang ada kecuali header
              for (let row = 1; row < worksheet.getMaxRows(); row++) {
                for (let col = 0; col < worksheet.getMaxColumns(); col++) {
                  worksheet.getRange(row, col).clear();
                }
              }

              // Masukkan data baru
              components.forEach((compObj, i) => {
                const rowIndex = i + 1;
                if (rowIndex >= worksheet.getMaxRows()) {
                  worksheet.insertRow(rowIndex, 1);
                }

                // Set nilai untuk setiap kolom
                worksheet.getRange(rowIndex, componentIndex).setValue(compObj.component || '');

                // Set kolom lainnya
                Object.entries(compObj).forEach(([key, value]) => {
                  const colIndex = columns.indexOf(key);
                  if (colIndex >= 0) {
                    worksheet.getRange(rowIndex, colIndex).setValue(value);
                  }
                });
              });

              // Terapkan kembali dropdown
              applyComponentDropdown();
              applyTypeDropdown();

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
  </script>
</div>
