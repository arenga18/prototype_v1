<iframe
  src="https://docs.google.com/spreadsheets/d/e/2PACX-1vRrO6UATHaD4wWE2UJ7jkx-2tYYb4YGHZ3CsMFSZhU8ijvJ5HMiqMz-iHFBjh2AobDiMGR7h6VXRrJn/pubhtml?widget=true&amp;headers=false"
  width="1000" height="1000"></iframe>

{{-- <div class="overflow-x-auto">
  <table class="table-auto border border-collapse w-max text-sm">
    <thead>
      <tr>
        @foreach (['Komponen', 'P', 'L', 'T', 'Qty', 'Sub', 'Jml', 'Bahan', 'T Bahan', 'L (kedua)', 'D', 'T (kedua)', 'Dalam', 'T (ketiga)', 'P1', 'P2', 'L1', 'L2', 'Profile', 'Rel', 'V', 'V2', 'H', 'Nama Barang', 'Panjang', 'V lap', 'V edg', 'Deskripsi Lapisan', 'Deskripsi Edging', 'Engsel', 'Rel (kedua)', 'Bahan Dasar', 'Jumlah Anodize', 'minifix', 'dowel', 'jml siku', 'jml screw'] as $header)
          @php
            $wideCols = ['P', 'L', 'T', 'Qty', 'Sub', 'Jml']; // kolom yang dilebarkan
            if ($header === 'Komponen') {
                $widthClass = 'w-48'; // lebih lebar untuk kolom Komponen
            } elseif (in_array($header, $wideCols)) {
                $widthClass = 'w-36'; // sekitar 9rem = 144px
            } else {
                $widthClass = 'w-28'; // sekitar 7rem = 112px untuk kolom lain
            }
          @endphp
          <th class="border px-2 py-1 whitespace-nowrap {{ $widthClass }} text-center">{{ $header }}</th>
        @endforeach
      </tr>
    </thead>

    <tbody>
      @php $globalRow = 0; @endphp
      @foreach ($groupedComponents as $modul => $components)
        @foreach ($components as $row => $comp)
          @php
            $isModulRow = $loop->first;
            $trClass = $isModulRow ? 'bg-gray-100 font-semibold' : '';
            $currentRow = $globalRow;
            $globalRow++;
          @endphp
          <tr class="{{ $trClass }}">
            <td class="border px-2 py-1 whitespace-nowrap w-48">
              @if ($isModulRow)
                {{ $modul }}
              @else
                {{ $comp['component'] ?? '' }}
              @endif
            </td>

            @php
              $inputColumns = [
                  'p',
                  'l',
                  't',
                  'qty',
                  'sub',
                  'jml',
                  'bahan',
                  't_bahan',
                  'l_kedua',
                  'd',
                  't_kedua',
                  'dalam',
                  't_ketiga',
                  'p1',
                  'p2',
                  'l1',
                  'l2',
                  'profile',
                  'rel',
                  'v',
                  'v2',
                  'h',
                  'nama_barang',
                  'panjang',
                  'v_lap',
                  'v_edg',
                  'deskripsi_lapisan',
                  'deskripsi_edging',
                  'engsel',
                  'rel_kedua',
                  'bahan_dasar',
                  'jumlah_anodize',
                  'minifix',
                  'dowel',
                  'jml_siku',
                  'jml_screw',
              ];
              $colToHeader = [
                  'p' => 'P',
                  'l' => 'L',
                  't' => 'T',
                  'qty' => 'Qty',
                  'sub' => 'Sub',
                  'jml' => 'Jml',
                  // kolom lain tidak perlu mapping karena width default
              ];
              $wideCols = ['P', 'L', 'T', 'Qty', 'Sub', 'Jml'];
            @endphp

            @foreach ($inputColumns as $col)
              @php
                $headerName = $colToHeader[$col] ?? null;
                if (in_array($headerName, $wideCols)) {
                    $widthClass = 'w-36';
                } else {
                    $widthClass = 'w-28';
                }
              @endphp
              <td class="border px-2 py-1 {{ $widthClass }} ">
                <input type="text" x-data
                  @keydown.enter.prevent="navigate('down', {{ $currentRow }}, '{{ $col }}')"
                  @keydown.arrow-down.prevent="navigate('down', {{ $currentRow }}, '{{ $col }}')"
                  @keydown.arrow-up.prevent="navigate('up', {{ $currentRow }}, '{{ $col }}')"
                  @keydown.arrow-left.prevent="navigate('left', {{ $currentRow }}, '{{ $col }}')"
                  @keydown.arrow-right.prevent="navigate('right', {{ $currentRow }}, '{{ $col }}')"
                  wire:model.lazy="groupedComponents.{{ $modul }}.{{ $row }}.{{ $col }}"
                  data-row="{{ $currentRow }}" data-col="{{ $col }}"
                  class="w-full border-none p-1 text-sm bg-transparent focus:outline-none focus:ring">
              </td>
            @endforeach

          </tr>
        @endforeach
      @endforeach
    </tbody>
  </table>
</div>

<script>
  // Daftar kolom sesuai inputColumns lengkap
  const cols = [
    'p', 'l', 't', 'qty', 'sub', 'jml', 'bahan', 't_bahan', 'l_kedua', 'd',
    't_kedua', 'dalam', 't_ketiga', 'p1', 'p2', 'l1', 'l2', 'profile', 'rel',
    'v', 'v2', 'h', 'nama_barang', 'panjang', 'v_lap', 'v_edg', 'deskripsi_lapisan',
    'deskripsi_edging', 'engsel', 'rel_kedua', 'bahan_dasar', 'jumlah_anodize',
    'minifix', 'dowel', 'jml_siku', 'jml_screw'
  ];

  // Fungsi navigasi keyboard
  function navigate(direction, row, col) {
    let newRow = row;
    let newCol = col;
    const idx = cols.indexOf(col);

    if (direction === 'down') {
      newRow = row + 1;
    } else if (direction === 'up') {
      newRow = row - 1;
    } else if (direction === 'left') {
      if (idx > 0) newCol = cols[idx - 1];
    } else if (direction === 'right') {
      if (idx < cols.length - 1) newCol = cols[idx + 1];
    }

    const next = document.querySelector(`input[data-row="${newRow}"][data-col="${newCol}"]`);
    if (next) next.focus();
  }

  // Alpine.js helper supaya bisa dipakai di x-data
  document.addEventListener('alpine:init', () => {
    Alpine.data('navigate', () => ({
      navigate: navigate
    }));
  });
</script> --}}
