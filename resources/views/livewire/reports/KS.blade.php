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
          // Extract all components from all modules
          $allComponents = collect($modulBreakdown)->flatMap(function ($module) {
              return collect($module['components'])->map(function ($component) use ($module) {
                  // Add Tpk from parent module to each component
                  $component['Tpk'] = $module['modul']['Tpk'] ?? '';
                  return $component;
              });
          });

          // Group by Tpk first
          $groupedByTpk = $allComponents->groupBy('Tpk');

          // Prepare data structure for pivot table
          $pivotData = [];
          $grandTotal = 0;

          foreach ($groupedByTpk as $tpk => $components) {
              // Group components by nama_komponen and ukuran within each Tpk
              $groupedByComponent = $components->groupBy(function ($item) {
                  return $item['nama_komponen'] . '|' . $item['ukuran'];
              });

              foreach ($groupedByComponent as $key => $group) {
                  $splitKey = explode('|', $key);
                  $nama_komponen = $splitKey[0];
                  $ukuran = $splitKey[1];
                  $count = $group->count();
                  $kode = $group->first()['kode'] ?? '';

                  $pivotData[] = [
                      'tpk' => $tpk,
                      'nama_komponen' => $nama_komponen,
                      'ukuran' => $ukuran,
                      'kode' => $kode,
                      'count' => $count,
                      'rowspan' => $count,
                      'items' => $group,
                  ];

                  $grandTotal += $count;
              }
          }
        @endphp

        @foreach ($pivotData as $index => $data)
          @foreach ($data['items'] as $itemIndex => $item)
            <tr>
              <td align="center">{{ $index + 1 }}</td>

              @if ($itemIndex === 0)
                <td rowspan="{{ $data['rowspan'] }}">{{ $data['nama_komponen'] }}</td>
              @endif

              <td align="center">{{ $data['ukuran'] }}</td>

              @if ($itemIndex === 0 && ($index === 0 || $pivotData[$index - 1]['tpk'] !== $data['tpk']))
                <td rowspan="{{ $groupedByTpk[$data['tpk']]->count() }}" align="center">
                  {{ $data['tpk'] }}
                </td>
              @endif

              <td align="center">{{ $data['kode'] }}</td>

              @if ($itemIndex === 0)
                <td rowspan="{{ $data['rowspan'] }}" align="center">{{ $data['count'] }}</td>
              @endif

              <td align="center"></td>
              <td align="center"></td>
            </tr>
          @endforeach
        @endforeach

        {{-- Grand Total Row --}}
        <tr>
          <td colspan="5" align="center"><b>Grand Total</b></td>
          <td align="center"><b>{{ $grandTotal }}</b></td>
          <td align="center"></td>
          <td align="center"></td>
        </tr>
      </tbody>
    </table>
  </div>
</body>

</html>
