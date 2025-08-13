<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Rekapitulasi Pemakaian Bahan</title>
  <style>
    @page {
      size: 8.5in 11in;
      margin: 0.1in;
      mso-header-margin: .5in;
      mso-footer-margin: .5in;
    }

    body {
      font-family: Arial, sans-serif;
      width: 7.5in;
      margin: 0 auto;
      font-size: 15px;
      mso-pagination: widow-orphan;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 10px;
      font-size: 13px;
    }

    th,
    td {
      border: 1px solid #000;
      padding: 4px;
    }

    .header-table td {
      border: none;
      padding: 2px 4px;
    }

    .section-title {
      text-align: center;
      font-weight: bold;
      margin: 10px 0;
      margin-bottom: 20px;
      font-size: 16px;
    }

    .checklist-project {
      display: flex;
      flex-direction: column;
    }

    ul {
      margin: 0;
      padding-left: 16px;
    }

    /* Style for editable cells */
    td[contenteditable="true"] {
      min-height: 20px;
    }

    .editable {
      cursor: pointer;
    }

    .save-btn {
      background-color: #4CAF50;
      color: white;
      padding: 8px 16px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      margin-top: 10px;
    }

    .save-btn:hover {
      background-color: #45a049;
    }
  </style>
</head>

<body>
  <div class="full-recap-report-wrapper">
    <div class="section-title"><u>REKAPITULASI PEMAKAIAN BAHAN</u></div>

    <table class="header-table">
      <tr>
        <td><strong>NO</strong></td>
        <td class="editable" contenteditable="true">{{ $spekData['recap_number'] }}</td>
        <td colspan="2">
          <strong>STATUS PROYEK</strong>
        </td>
      </tr>
      <tr>
        <td><strong>NO KONTRAK</strong></td>
        <td class="editable" contenteditable="true">{{ $spekData['no_contract'] }}</td>
        <td align="center" style="border: 1px solid black">
          @if (in_array('Pendingan', $spekData['project_status'] ?? []))
            ✓
          @endif
        </td>
        <td align="center" style="border: 1px solid black">
          ADA PENDINGAN
        </td>
      </tr>
      <tr>
        <td><strong>NIP</strong></td>
        <td class="editable" contenteditable="true">{{ $spekData['nip'] }}</td>
        <td align="center" style="border: 1px solid black">
          @if (!in_array('Pendingan', $spekData['project_status'] ?? []))
            ✓
          @endif
        </td>
        <td align="center" style="border: 1px solid black">
          TIDAK ADA PEND
        </td>
      </tr>
      <tr>
        <td><strong>PROYEK</strong></td>
        <td class="editable" contenteditable="true">{{ $spekData['project_name'] }}-{{ $spekData['product_name'] }}</td>
      </tr>
      <tr>
        <td><strong>TGL. ORDER</strong></td>
        <td class="editable" contenteditable="true">{{ $spekData['date'] }}</td>
        <td colspan="2"><strong>ANTI RAYAP/TIDAK</strong></td>
      </tr>
      <tr>
        <td><strong>TGL. SELESAI</strong></td>
        <td class="editable" contenteditable="true"></td>
        <td align="center" style="border: 1px solid black">
          @if (!in_array('Anti Rayap', $spekData['project_status'] ?? []))
            ✓
          @endif
        </td>
        <td align="center" style="border: 1px solid black">
          @if (in_array('Anti Rayap', $spekData['project_status'] ?? []))
            ✓
          @endif
        </td>
      </tr>
      <tr>
        <td><strong>ESTIMATOR PPIC</strong></td>
        <td class="editable" contenteditable="true">{{ $spekData['estimator'] }}</td>
      </tr>
      <tr>
        <td><strong>KOORDINATOR REKAP</strong></td>
        <td class="editable" contenteditable="true">{{ $spekData['recap_coordinator'] }}</td>
      </tr>
    </table>

    <table id="mainTable">
      <thead>
        <tr>
          <th></th>
          <th colspan="8" align="left">Rekap</th>
        </tr>
        <tr>
          <th>No</th>
          <th>Nama Komponen</th>
          <th>Ukuran</th>
          <th>Tpk</th>
          <th>kode</th>
          <th>Total</th>
          <th>✓<br>QC</th>
          <th>KETERANGAN</th>
        </tr>
      </thead>
      <tbody>
        @php
          // Initialize variables
          $filteredComponents = [];
          $componentIndex = 1;
          $grandTotalQty = 0;

          // First pass: Filter components with kode = "(ks)"
          foreach ($modulBreakdown as $module) {
              if (!isset($module['components'])) {
                  continue;
              }

              $moduleName = $module['components'][0]['nama_modul'] ?? '';
              $moduleTpk = $module['components'][0]['Tpk'] ?? '';
              $moduleKode = $module['components'][0]['kode'] ?? '';

              foreach ($module['components'] as $component) {
                  if (isset($component['kode']) && strtoupper($component['kode']) === '(KS)') {
                      $qty = $component['jml'] ?? 1;
                      $grandTotalQty += $qty;

                      $filteredComponents[] = [
                          'module_name' => $moduleName,
                          'module_tpk' => $moduleTpk,
                          'module_kode' => $moduleKode,
                          'component_name' => $component['component'] ?? '',
                          'component_size' => $component['ukuran'] ?? '',
                          'component_qty' => $qty,
                          'component_data' => $component,
                      ];
                  }
              }
          }

          // Group by module name and Tpk
          $groupedData = collect($filteredComponents)->groupBy(['module_tpk', 'module_name']);

          // Prepare display data with rowspans
          $displayData = [];
          $currentIndex = 1;

          foreach ($groupedData as $tpk => $modules) {
              foreach ($modules as $moduleName => $components) {
                  $rowspan = $components->count();

                  foreach ($components as $index => $comp) {
                      $displayData[] = [
                          'index' => $currentIndex++,
                          'module_name' => $moduleName,
                          'component_name' => $comp['component_name'],
                          'component_size' => $comp['component_size'],
                          'component_qty' => $comp['component_qty'],
                          'tpk' => $tpk,
                          'kode' => $comp['module_kode'],
                          'is_first' => $index === 0,
                          'rowspan' => $rowspan,
                          'module_rowspan' => $rowspan,
                      ];
                  }
              }
          }
        @endphp

        @foreach ($displayData as $item)
          <tr>
            <td align="center">{{ $item['index'] }}</td>

            @if ($item['is_first'])
              <td rowspan="{{ $item['module_rowspan'] }}" class="editable" contenteditable="true">
                {{ $item['module_name'] }}</td>
            @endif

            <td align="center" class="editable" contenteditable="true">{{ $item['component_size'] }}</td>

            @if ($item['is_first'])
              <td rowspan="{{ $item['module_rowspan'] }}" align="center" class="editable" contenteditable="true">
                {{ $item['tpk'] }}
              </td>
            @endif

            <td align="center" class="editable" contenteditable="true">{{ $item['kode'] }}</td>
            <td align="center" class="editable" contenteditable="true">{{ $item['component_qty'] }}</td>
            <td align="center" class="editable" contenteditable="true"></td>
            <td align="center" class="editable" contenteditable="true"></td>
          </tr>
        @endforeach

        {{-- Grand Total Row --}}
        <tr>
          <td colspan="5" align="center"><b>Grand Total</b></td>
          <td align="center"><b>{{ $grandTotalQty }}</b></td>
          <td align="center"></td>
          <td align="center"></td>
        </tr>
      </tbody>
    </table>

    <button class="save-btn" id="saveData">Simpan Perubahan</button>
  </div>

  <script>
    const modulBreakdown = @json($modulBreakdown);
    console.log("Modul Breakdown : ", modulBreakdown);

    // Make cells editable
    document.addEventListener('DOMContentLoaded', function() {
      // Highlight editable cells on hover
      const editableCells = document.querySelectorAll('[contenteditable="true"]');
      editableCells.forEach(cell => {});

      // Save button functionality
      document.getElementById('saveData').addEventListener('click', function() {
        // Collect all editable data
        const editableData = {
          header: {},
          tableData: []
        };

        // Get header data
        const headerCells = document.querySelectorAll('.header-table [contenteditable="true"]');
        headerCells.forEach(cell => {
          const key = cell.parentElement.querySelector('strong').textContent.trim();
          editableData.header[key] = cell.textContent.trim();
        });

        // Get table data
        const rows = document.querySelectorAll('#mainTable tbody tr:not(:last-child)');
        rows.forEach(row => {
          const cells = row.querySelectorAll('td');
          if (cells.length >= 8) {
            editableData.tableData.push({
              index: cells[0].textContent.trim(),
              module_name: cells[1]?.textContent.trim() || '',
              component_size: cells[2]?.textContent.trim() || '',
              tpk: cells[3]?.textContent.trim() || '',
              kode: cells[4]?.textContent.trim() || '',
              qty: cells[5]?.textContent.trim() || '',
              qc: cells[6]?.textContent.trim() || '',
              keterangan: cells[7]?.textContent.trim() || ''
            });
          }
        });
      });
    });
  </script>
</body>

</html>
