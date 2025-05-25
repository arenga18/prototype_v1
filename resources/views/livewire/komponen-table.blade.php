<div style="width: 100%; overflow-x: scroll;">
  <!-- Stylesheets and scripts -->
  <script src="https://bossanova.uk/jspreadsheet/v5/jspreadsheet.js"></script>
  <script src="https://jsuites.net/v5/jsuites.js"></script>
  <link rel="stylesheet" href="https://bossanova.uk/jspreadsheet/v5/jspreadsheet.css" />
  <link rel="stylesheet" href="https://jsuites.net/v5/jsuites.css" />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Material+Icons" />

  <div id="spreadsheet" style="width:100%;"></div>
  <button onclick="saveSheet()">Simpan</button>

  <script>
    const groupedComponents = @json($groupedComponents ?? []);
    const columns = @json($columns ?? []);
    const namaModulIndex = columns.indexOf('nama_modul'); // Find component name column index
    const componentIndex = columns.indexOf('component'); // Find component column index

    // Prepare data with all columns
    let data = [];
    let mergeCellsConfig = {}; // Object to store merge configurations

    Object.entries(groupedComponents).forEach(([modul, components], modulIndex) => {
      // Track the starting row for this modul
      const modulStartRow = data.length;

      // Add modul name row (only nama_modul column filled)
      let modulRow = new Array(columns.length).fill('');
      modulRow[namaModulIndex] = modul; // Modul name in nama_modul column
      data.push(modulRow);

      // Parse the components if they're in JSON format
      if (typeof components === 'string') {
        try {
          components = JSON.parse(components);
        } catch (e) {
          console.error('Error parsing component JSON:', e);
          components = [];
        }
      }

      // Count actual components (non-empty)
      let componentCount = 0;

      // Add each component (only component column filled)
      if (Array.isArray(components)) {
        components.forEach(comp => {
          let componentRow = new Array(columns.length).fill('');

          // Fill component column
          if (comp.component) {
            componentRow[componentIndex] = comp.component;
          } else if (typeof comp === 'string') {
            componentRow[componentIndex] = comp;
          } else if (comp[columns[componentIndex]]) {
            componentRow[componentIndex] = comp[columns[componentIndex]];
          }

          // Skip if component is empty
          if (!componentRow[componentIndex]) {
            return;
          }

          // Copy other properties if they exist in the component object
          columns.forEach((col, index) => {
            if (comp[col] && index !== componentIndex) {
              componentRow[index] = comp[col];
            }
          });

          data.push(componentRow);
          componentCount++;
        });
      }

      // Add merge configuration if there are components
      if (componentCount > 0) {
        const modulCell = `${String.fromCharCode(65 + namaModulIndex)}${modulStartRow + 1}`;
        mergeCellsConfig[modulCell] = [1, componentCount + 1];
      }

      // Add empty row separator except after last modul
      if (modulIndex < Object.keys(groupedComponents).length - 1) {
        data.push(new Array(columns.length).fill(''));
      }
    });

    // Prepare column definitions for all columns
    let columnDefs = columns.map((col, index) => {
      let columnDef = {
        title: col,
        width: index === namaModulIndex ? 300 : (index === componentIndex ? 400 : 60),
        readonly: index !== componentIndex // Only component column is editable
      };

      // Add special formatting for specific columns
      if (col === 'type') {
        columnDef.type = 'dropdown';
        columnDef.source = ['Option 1', 'Option 2', 'Option 3'];
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
        freezeColumns: 5,
        freezeRows: 5,
        mergeCells: mergeCellsConfig, // Dynamic merge configuration
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
        // If row has modul name (in nama_modul column), it's a new group
        if (row[namaModulIndex]) {
          // Save previous modul's components if exists
          if (currentModul && componentsForCurrentModul.length > 0) {
            updatedComponents[currentModul] = componentsForCurrentModul;
          }
          // Start new modul group
          currentModul = row[namaModulIndex];
          componentsForCurrentModul = [];
        }
        // If row has component data and we're in a modul group
        else if (row[componentIndex] && currentModul) {
          // Create component object with all columns
          let componentObj = {};
          columns.forEach((col, colIndex) => {
            if (row[colIndex]) {
              componentObj[col] = row[colIndex];
            }
          });
          componentsForCurrentModul.push(componentObj);
        }
      });

      // Save the last modul's components
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
