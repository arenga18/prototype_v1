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
  </style>
</head>

<body>
  <div class="full-recap-report-wrapper">
    <div class="section-title"><u>REKAPITULASI PEMAKAIAN BAHAN (KS)</u></div>

    <table class="header-table">
      <tr>
        <td><strong>NO</strong></td>
        <td>{{ $spekData['recap_number'] }}</td>
        <td colspan="2">
          <strong>STATUS PROYEK</strong>
        </td>
      </tr>
      <tr>
        <td><strong>NO KONTRAK</strong></td>
        <td>{{ $spekData['no_contract'] }}</td>
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
        <td>{{ $spekData['nip'] }}</td>
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
        <td>{{ $spekData['project_name'] }}-{{ $spekData['product_name'] }}</td>
      </tr>
      <tr>
        <td><strong>TGL. ORDER</strong></td>
        <td>{{ $spekData['date'] }}</td>
        <td colspan="2"><strong>ANTI RAYAP/TIDAK</strong></td>
      </tr>
      <tr>
        <td><strong>TGL. SELESAI</strong></td>
        <td></td>
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
        <td>{{ $spekData['estimator'] }}</td>
      </tr>
      <tr>
        <td><strong>KOORDINATOR REKAP</strong></td>
        <td>{{ $spekData['recap_coordinator'] }}</td>
      </tr>
    </table>

    <table>
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
              <td rowspan="{{ $item['module_rowspan'] }}">{{ $item['module_name'] }}</td>
            @endif

            <td align="center">{{ $item['component_size'] }}</td>

            @if ($item['is_first'])
              <td rowspan="{{ $item['module_rowspan'] }}" align="center">
                {{ $item['tpk'] }}
              </td>
            @endif

            <td align="center">{{ $item['kode'] }}</td>
            <td align="center">{{ $item['component_qty'] }}</td>
            <td align="center"></td>
            <td align="center"></td>
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
  </div>
  <script>
    const modulBreakdown = @json($modulBreakdown);
    console.log("Modul Breakdown : ", modulBreakdown);
  </script>
</body>


</html>
