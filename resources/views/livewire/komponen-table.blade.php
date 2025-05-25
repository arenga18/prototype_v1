<div style="width: 100%; height: 70vh;">
  <!-- Stylesheets and scripts -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/luckysheet@latest/dist/plugins/css/pluginsCss.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/luckysheet@latest/dist/plugins/plugins.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/luckysheet@latest/dist/css/luckysheet.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/luckysheet@latest/dist/assets/iconfont/iconfont.css" />
  <script src="https://cdn.jsdelivr.net/npm/luckysheet@latest/dist/plugins/js/plugin.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/luckysheet@latest/dist/luckysheet.umd.js"></script>

  <div id="luckysheet" style="margin: 0; padding: 0; width: 100%; height: 100%;"></div>
  <button onclick="saveSheet()">Simpan</button>

  <script>
    const groupedComponents = @json($groupedComponents);
    const columns = @json($columns);


    let celldata = [];

    // Header row
    columns.forEach((col, idx) => {
      celldata.push({
        r: 0,
        c: idx,
        v: {
          v: col
        }
      });
    });

    // Component rows
    let rowIndex = 1;
    Object.keys(groupedComponents).forEach(modul => {
      groupedComponents[modul].forEach(comp => {
        columns.forEach((col, colIndex) => {
          let val = comp[col] !== undefined ? comp[col] : '';
          celldata.push({
            r: rowIndex,
            c: colIndex,
            v: {
              v: val
            }
          });
        });
        rowIndex++;
      });
    });

    // Dropdown setup
    let dataVerification = {};
    columns.forEach((col, idx) => {
      const colLetter = String.fromCharCode(65 + idx); // 'A', 'B', ...
      if (col === 'type') {
        dataVerification[`${colLetter}2:${colLetter}1000`] = {
          type: 'dropdown',
          value1: typeOptions.join(','),
          allowBlank: true,
          showInputMessage: true,
          prompt: 'Pilih type'
        };
      }
      if (col === 'component') {
        dataVerification[`${colLetter}2:${colLetter}1000`] = {
          type: 'dropdown',
          value1: componentOptions.join(','),
          allowBlank: true,
          showInputMessage: true,
          prompt: 'Pilih komponen'
        };
      }
    });

    // Init luckysheet
    luckysheet.create({
      container: 'luckysheet',
      data: [{
        name: 'Komponen',
        celldata: celldata,
        config: {
          dataVerification
        }
      }]
    });

    // Save handler
    function saveSheet() {
      const sheetsData = luckysheet.getAllSheets();
      const btn = document.querySelector('button[onclick="saveSheet()"]');
      const originalText = btn.textContent;

      btn.textContent = 'Menyimpan...';
      btn.disabled = true;

      fetch('/save-luckysheet', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
          },
          body: JSON.stringify({
            modul_id: modulId,
            name: 'Sheet Komponen',
            data: JSON.stringify(sheetsData)
          })
        })
        .then(res => {
          if (!res.ok) throw new Error('Gagal menyimpan');
          return res.json();
        })
        .then(data => {
          if (data.success) {
            alert('Berhasil disimpan! ID: ' + data.id);
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
