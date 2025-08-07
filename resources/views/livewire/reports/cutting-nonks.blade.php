<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Cutting order (Non KS)</title>
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
    <div class="section-title"><u>CUTTING ORDER (Non KS)</u></div>

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
          <th colspan="5" align="left">Rekap</th>
        </tr>
        <tr>
          <th>kode</th>
          <th>Deskripsi Bahan</th>
          <th>Komponen</th>
          <th>P</th>
          <th>L</th>
          <th>Total</th>
          <th>KETERANGAN</th>
        </tr>
      </thead>
      <tbody>
        @php
          $grandTotalQty = 0;

          // Get all components with kode matching the specified values
          $components = collect($modulBreakdown)
              ->flatMap(function ($module) {
                  return collect($module['components'])
                      ->filter(function ($component) {
                          $kode = $component['kode'] ?? '';
                          return $kode === '(ks)' || $kode === '[ks]' || $kode === 'k¢' || $kode === '─';
                      })
                      ->map(function ($component) use ($module) {
                          return [
                              'kode' => $component['kode'] ?? '',
                              'deskripsi_bahan' => $component['deskripsi_bahan'] ?? '',
                              'nama_modul' => $component['nama_modul'] ?? '',
                              'component' => $component['component'] ?? '',
                              'P' => $component['P'] ?? '',
                              'L' => $component['L'] ?? '',
                              'jml' => $component['jml'] ?? 1,
                          ];
                      });
              })
              ->groupBy('kode');

          $grandTotal = $components
              ->flatMap(function ($group) {
                  return $group;
              })
              ->sum('jml');
        @endphp

        @foreach ($components as $kode => $group)
          @php
            $rowCount = count($group);
            $isFirstRow = true;
            $rowNumber = $loop->iteration;
          @endphp

          @foreach ($group as $index => $component)
            <tr>
              <td align="center">{{ $component['kode'] }}</td>
              <td align="center" style="width: 110px;">{{ $component['deskripsi_bahan'] }}</td>
              <td align="center">
                @if (!empty($component['nama_modul']))
                  {{ $component['nama_modul'] }}
                @else
                  {{ $component['component'] }}
                @endif
              </td>
              <td align="center">{{ $component['P'] }}</td>
              <td align="center">{{ $component['L'] }}</td>
              <td align="center">{{ $component['jml'] }}</td>
              <td align="center"></td>
            </tr>
            @php
              $isFirstRow = false;
            @endphp
          @endforeach
        @endforeach
        <tr>
          <td colspan="5" align="center"><b>Grand Total</b></td>
          <td align="center"><b>{{ $grandTotal }}</b></td>
          <td align="center"></td>
        </tr>
      </tbody>
    </table>
  </div>

  <script>
    const breakdownModul = @json($modulBreakdown);
    console.log(breakdownModul);
  </script>
</body>

</html>
