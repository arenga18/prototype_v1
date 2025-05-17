<div>
    <table class="table-auto w-full border border-gray-300">
    <thead class="bg-gray-100">
        <tr>
            <th class="border px-2 py-1">Komponen</th>
            <th class="border px-2 py-1">P</th>
            <th class="border px-2 py-1">L</th>
            <th class="border px-2 py-1">T</th>
            <th class="border px-2 py-1">Sub Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($komponen as $index => $row)
            <tr>
                <td class="border px-2 py-1">{{ $row['component'] }}</td>
                <td class="border px-2 py-1">
                    <input type="text" class="w-full" wire:model.lazy="komponen.{{ $index }}.p_value">
                </td>
                <td class="border px-2 py-1">
                    <input type="text" class="w-full" wire:model.lazy="komponen.{{ $index }}.l_value">
                </td>
                <td class="border px-2 py-1">
                    <input type="text" class="w-full" wire:model.lazy="komponen.{{ $index }}.t_value">
                </td>
                <td class="border px-2 py-1">
                    {{ ($row['p_value'] ?? 0) * ($row['l_value'] ?? 0) * ($row['t_value'] ?? 0) }}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

</div>
