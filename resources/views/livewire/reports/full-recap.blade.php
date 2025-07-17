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
    <div class="section-title"><u>REKAPITULASI PEMAKAIAN BAHAN</u></div>

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
          // Grouping data by Tpk
          $groupedByTpk = collect($modulBreakdown)->groupBy('modul.Tpk');

          $groupedByModul = $groupedByTpk->map(function ($tpkGroup) {
              return $tpkGroup->groupBy('modul.nama_modul')->map(function ($modulGroup) {
                  return [
                      'count' => $modulGroup->count(),
                      'items' => $modulGroup,
                      'subtotal' => $modulGroup->sum('total'),
                  ];
              });
          });

          // Calculate grand total
          $grandTotal = collect($modulBreakdown)->count();
        @endphp

        @foreach ($groupedByModul as $tpk => $modulGroups)
          @php $firstTpk = true; @endphp
          @foreach ($modulGroups as $modulName => $modulData)
            @php $firstModul = true; @endphp
            @foreach ($modulData['items'] as $index => $modul)
              <tr>
                <td></td>
                <td>
                  @if ($firstModul)
                    {{ $modulName }}
                  @endif
                </td>
                <td align="center">{{ $modul['modul']['ukuran'] ?? '' }}</td>

                {{-- Show Tpk only once with rowspan --}}
                @if ($firstTpk && $firstModul)
                  <td rowspan="{{ $modulGroups->sum(fn($group) => $group['count']) }}" align="center">
                    {{ $tpk }}
                  </td>
                  @php $firstTpk = false; @endphp
                @endif

                <td align="center">{{ $modul['modul']['kode'] ?? '' }}</td>

                {{-- Show total count only once per module --}}
                @if ($firstModul)
                  <td rowspan="{{ $modulData['count'] }}" align="center">{{ $modulData['count'] }}</td>
                  @php $firstModul = false; @endphp
                @endif

                <td align="center"></td>
                <td align="center"></td>
              </tr>
            @endforeach
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
