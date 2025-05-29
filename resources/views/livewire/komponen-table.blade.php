<div style="width: 100%; overflow-x: scroll;">
  <!-- Stylesheets and scripts -->
  <script src="https://bossanova.uk/jspreadsheet/v5/jspreadsheet.js"></script>
  <script src="https://jsuites.net/v5/jsuites.js"></script>
  <link rel="stylesheet" href="https://bossanova.uk/jspreadsheet/v5/jspreadsheet.css" />
  <link rel="stylesheet" href="https://jsuites.net/v5/jsuites.css" />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Material+Icons" />

  <div id="spreadsheet" style="width:100%; font-size:14px;"></div>

  <script>
    const groupedComponents = @json($groupedComponents ?? []);
    const columns = @json($columns ?? []);
    const namaModulIndex = columns.indexOf('nama_modul');
    const componentIndex = columns.indexOf('component');
    const componentTypes = @json($componentTypes ?? []);
    const componentOptions = @json($componentOptions ?? []);

    // Fungsi untuk memetakan data ke kolom spreadsheet
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

      // Isi kolom component
      componentRow[componentIndex] = comp.component || comp.name || '';

      // Mapping data lainnya
      Object.entries(fieldMapping).forEach(([sourceField, targetColumn]) => {
        const colIndex = columns.indexOf(targetColumn);
        if (colIndex >= 0 && comp[sourceField] !== undefined && comp[sourceField] !== null) {
          componentRow[colIndex] = comp[sourceField];
        }
      });

      // Isi kolom lain yang mungkin ada di data
      columns.forEach((col, index) => {
        if (comp[col] && index !== componentIndex) {
          componentRow[index] = comp[col];
        }
      });

      // Hitung ukuran jika ada p dan l
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

    // Prepare data with all columns
    let data = [];
    let mergeCellsConfig = {};

    Object.entries(groupedComponents).forEach(([modul, components], modulIndex) => {
      const modulStartRow = data.length;

      // Add modul name row
      let modulRow = new Array(columns.length).fill('');
      modulRow[namaModulIndex] = modul;
      data.push(modulRow);

      // Count actual components
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

      // Add merge configuration
      if (componentCount > 0) {
        const modulCell = `${String.fromCharCode(65 + namaModulIndex)}${modulStartRow + 1}`;
        mergeCellsConfig[modulCell] = [1, componentCount + 1];
      }

      // Add empty row separator except after last modul
      if (modulIndex < Object.keys(groupedComponents).length - 1) {
        data.push(new Array(columns.length).fill(''));
      }
    });

    // Prepare column definitions
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
                // Update semua kolom yang sesuai
                Object.entries(selectedComponent.data).forEach(([key, val]) => {
                  const colIndex = columns.indexOf(key);
                  if (colIndex >= 0) {
                    hot.setDataAtCell(row, colIndex, val);
                  }
                });
              }
            }
          }
        };
      } else if (col === 'no' || col === 'jml' || col === 'jumlah') {
        columnDef.type = 'numeric';
      }

      return columnDef;
    });

    // Initialize jspreadsheet
    const spreadsheet = jspreadsheet(document.getElementById('spreadsheet'), {
      toolbar: true,
      worksheets: [{
        name: 'Components',
        data: data,
        columns: columnDefs,
        minDimensions: [columns.length, Math.max(10, data.length)],
        freezeColumns: 7,
        freezeRows: 7,
        mergeCells: mergeCellsConfig,
        allowInsertColumn: false,
        allowDeleteColumn: false,
        tableOverflow: true,
        tableWidth: "100%",
      }],
    });

    // Save handler
    function saveSheet() {
      const sheetsData = spreadsheet.getData();
      const btn = document.querySelector('button[onclick="saveSheet()"]');
      const originalText = btn.textContent;

      btn.textContent = 'Menyimpan...';
      btn.disabled = true;

      // Reconstruct the original components data
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

      fetch('/save-luckysheet', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
          },
          body: JSON.stringify({
            modul_id: '{{ $moduls[0] ?? '' }}',
            name: 'Sheet Komponen',
            data: JSON.stringify(sheetsData),
            components: updatedComponents
          })
        })
        .then(res => {
          if (!res.ok) throw new Error('Gagal menyimpan');
          return res.json();
        })
        .then(data => {
          if (data.success) {
            alert('Berhasil disimpan!');
          } else {
            throw new Error(data.message || 'Gagal menyimpan');
          }
        })
        .catch(error => {
          alert('Error: ' + error.message);
        })
        .finally(() => {
          btn.textContent = originalText;
          btn.disabled = false;
        });
    }
  </script>
</div>
