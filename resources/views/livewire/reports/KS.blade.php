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

    <table border="1" cellpadding="5" cellspacing="0">
      <thead>
        <tr>
          <th></th>
          <th colspan="8" align="left">Rekap
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
          // Filter components with kode 'KS' and is_module = false
          $allComponents = collect($modulBreakdown)->flatMap(function ($module) {
              return collect($module['components'])
                  ->filter(function ($component) {
                      return ($component['kode'] ?? '') === 'KS' && ($component['is_module'] ?? false) === false;
                  })
                  ->map(function ($component) {
                      $component['Tpk'] = $component['Tpk'] ?? '';
                      $component['jml'] = $component['jml'] ?? 1; // Default to 1 if not set
                      return $component;
                  });
          });

          // Group by component name first
          $groupedByName = $allComponents->groupBy('nama_komponen');

          $tableData = [];
          $grandTotal = 0;
          $rowNumber = 1;

          foreach ($groupedByName as $name => $components) {
              // Group by size within each component name
              $groupedBySize = $components->groupBy('ukuran');

              $nameRowspan = $components->sum('jml');
              $isFirstName = true;

              foreach ($groupedBySize as $size => $sizeComponents) {
                  $sizeRowspan = $sizeComponents->sum('jml');
                  $sizeTotal = $sizeComponents->sum('jml');
                  $isFirstSize = true;

                  // Group by Tpk within each size
                  $groupedByTpk = $sizeComponents->groupBy('Tpk');

                  foreach ($groupedByTpk as $tpk => $tpkComponents) {
                      $tpkRowspan = $tpkComponents->count();
                      $isFirstTpk = true;

                      foreach ($tpkComponents as $component) {
                          $tableData[] = [
                              'row_number' => $isFirstName ? $rowNumber : '',
                              'nama_komponen' => $isFirstName ? $name : '',
                              'name_rowspan' => $isFirstName ? $nameRowspan : 0,
                              'ukuran' => $isFirstSize ? $size : '',
                              'size_rowspan' => $isFirstSize ? $sizeRowspan : 0,
                              'tpk' => $tpk,
                              'tpk_rowspan' => $isFirstTpk ? $tpkRowspan : 0,
                              'kode' => $component['kode'],
                              'count' => $isFirstSize ? $sizeTotal : '',
                              'jml' => $component['jml'],
                              'is_first_name' => $isFirstName,
                              'is_first_size' => $isFirstSize,
                              'is_first_tpk' => $isFirstTpk,
                          ];

                          $grandTotal += $component['jml'];
                          $isFirstTpk = false;
                          $isFirstSize = false;
                          $isFirstName = false;
                      }
                  }
              }

              $rowNumber++;
          }
        @endphp

        @if (count($tableData) > 0)
          @foreach ($tableData as $data)
            <tr>
              @if ($data['is_first_name'])
                <td align="center" rowspan="{{ $data['name_rowspan'] }}">{{ $data['row_number'] }}</td>
                <td align="center" rowspan="{{ $data['name_rowspan'] }}">{{ $data['nama_komponen'] }}</td>
              @endif
              @if ($data['is_first_size'])
                <td align="center" rowspan="{{ $data['size_rowspan'] }}" style="width: 110px">
                  {{ $data['ukuran'] }}
                </td>
              @endif
              @if ($data['is_first_tpk'])
                <td align="center" rowspan="{{ $data['tpk_rowspan'] }}">{{ $data['tpk'] }}</td>
              @endif
              <td align="center">{{ $data['kode'] }}</td>
              @if ($data['is_first_size'])
                <td align="center" rowspan="{{ $data['size_rowspan'] }}">{{ $data['count'] }}</td>
              @endif
              <td align="center"></td>
              <td align="center"></td>
            </tr>
          @endforeach

          <tr>
            <td colspan="5" align="center"><b>Grand Total (KS)</b></td>
            <td align="center"><b>{{ $grandTotal }}</b></td>
            <td align="center"></td>
            <td align="center"></td>
          </tr>
        @else
          <tr>
            <td colspan="8" align="center">Tidak ada data dengan kode KS</td>
          </tr>
        @endif
      </tbody>
    </table>
  </div>

  <script>
    const breakdownModul = @json($modulBreakdown);
    console.log(breakdownModul);
  </script>
</body>

</html>
