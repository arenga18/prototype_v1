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
          // Filter modules with kode = "(ks)"
          $filteredModulBreakdown = collect($modulBreakdown)->filter(function ($modul) {
              return isset($modul['modul']['kode']) && strtoupper($modul['modul']['kode']) === '(KS)';
          });

          // Group by Tpk first
          $groupedByTpk = $filteredModulBreakdown->groupBy('modul.Tpk');

          // Prepare data for merging same component names
          $mergedComponents = [];
          $rowspans = [];

          foreach ($groupedByTpk as $tpk => $components) {
              // Group components by name within each Tpk group
              $groupedByName = $components->groupBy('modul.nama_modul');

              foreach ($groupedByName as $name => $items) {
                  $rowspan = $items->count();
                  $rowspans[$tpk][$name] = $rowspan;

                  foreach ($items as $index => $item) {
                      $mergedComponents[] = [
                          'tpk' => $tpk,
                          'name' => $name,
                          'data' => $item,
                          'is_first' => $index === 0,
                          'rowspan' => $rowspan,
                      ];
                  }
              }
          }

          $grandTotal = $filteredModulBreakdown->count();
        @endphp

        @foreach ($mergedComponents as $index => $component)
          <tr>
            <td align="center">{{ $index + 1 }}</td>

            @if ($component['is_first'])
              <td rowspan="{{ $component['rowspan'] }}">{{ $component['name'] }}</td>
            @endif

            <td align="center">{{ $component['data']['modul']['ukuran'] ?? '' }}</td>

            @if ($component['is_first'] && ($index === 0 || $mergedComponents[$index - 1]['tpk'] !== $component['tpk']))
              <td rowspan="{{ $groupedByTpk[$component['tpk']]->count() }}" align="center">
                {{ $component['tpk'] }}
              </td>
            @endif

            <td align="center">{{ $component['data']['modul']['kode'] ?? '' }}</td>
            <td align="center">1</td>
            <td align="center"></td>
            <td align="center"></td>
          </tr>
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
