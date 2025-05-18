<div x-data="{
    komponen: @entangle('komponen')
}" class="overflow-x-auto w-full">
    <table class="table-auto border-collapse border border-gray-300 text-sm">
        <thead>
            <tr class="bg-gray-200">
                <th class="border border-gray-300 px-3 py-1 text-left whitespace-nowrap">Komponen</th>
                <th class="border border-gray-300 px-3 py-1 text-center whitespace-nowrap">P</th>
                <th class="border border-gray-300 px-3 py-1 text-center whitespace-nowrap">L</th>
                <th class="border border-gray-300 px-3 py-1 text-center whitespace-nowrap">T</th>
                <th class="border border-gray-300 px-3 py-1 text-center whitespace-nowrap">Qty</th>
                <th class="border border-gray-300 px-3 py-1 text-center whitespace-nowrap">Sub</th>
                <th class="border border-gray-300 px-3 py-1 text-center whitespace-nowrap">Jml</th>
                <th class="border border-gray-300 px-3 py-1 text-center whitespace-nowrap">Bahan</th>
                <th class="border border-gray-300 px-3 py-1 text-center whitespace-nowrap">T Bahan</th>
                <th class="border border-gray-300 px-3 py-1 text-center whitespace-nowrap">L</th>
                <th class="border border-gray-300 px-3 py-1 text-center whitespace-nowrap">D</th>
                <th class="border border-gray-300 px-3 py-1 text-center whitespace-nowrap">T</th>
                <th class="border border-gray-300 px-3 py-1 text-center whitespace-nowrap">Dalam</th>
                <th class="border border-gray-300 px-3 py-1 text-center whitespace-nowrap">T</th>
                <th class="border border-gray-300 px-3 py-1 text-center whitespace-nowrap">P1</th>
                <th class="border border-gray-300 px-3 py-1 text-center whitespace-nowrap">P2</th>
                <th class="border border-gray-300 px-3 py-1 text-center whitespace-nowrap">L1</th>
                <th class="border border-gray-300 px-3 py-1 text-center whitespace-nowrap">L2</th>
                <th class="border border-gray-300 px-3 py-1 text-center whitespace-nowrap">Profile</th>
                <th class="border border-gray-300 px-3 py-1 text-center whitespace-nowrap">Rel</th>
                <th class="border border-gray-300 px-3 py-1 text-center whitespace-nowrap">V</th>
                <th class="border border-gray-300 px-3 py-1 text-center whitespace-nowrap">V2</th>
                <th class="border border-gray-300 px-3 py-1 text-center whitespace-nowrap">H</th>
                <th class="border border-gray-300 px-3 py-1 text-center whitespace-nowrap">Nama Barang</th>
                <th class="border border-gray-300 px-3 py-1 text-center whitespace-nowrap">Panjang</th>
                <th class="border border-gray-300 px-3 py-1 text-center whitespace-nowrap">V lap</th>
                <th class="border border-gray-300 px-3 py-1 text-center whitespace-nowrap">V edg</th>
                <th class="border border-gray-300 px-3 py-1 text-center whitespace-nowrap">Deskripsi Lapisan</th>
                <th class="border border-gray-300 px-3 py-1 text-center whitespace-nowrap">Deskripsi Edging</th>
                <th class="border border-gray-300 px-3 py-1 text-center whitespace-nowrap">Engsel</th>
                <th class="border border-gray-300 px-3 py-1 text-center whitespace-nowrap">Rel</th>
                <th class="border border-gray-300 px-3 py-1 text-center whitespace-nowrap">Bahan Dasar</th>
                <th class="border border-gray-300 px-3 py-1 text-center whitespace-nowrap">Jumlah Anodize</th>
                <th class="border border-gray-300 px-3 py-1 text-center whitespace-nowrap">minifix</th>
                <th class="border border-gray-300 px-3 py-1 text-center whitespace-nowrap">dowel</th>
                <th class="border border-gray-300 px-3 py-1 text-center whitespace-nowrap">jml siku</th>
                <th class="border border-gray-300 px-3 py-1 text-center whitespace-nowrap">jml screw</th>
            </tr>
        </thead>
        <tbody>
            <template x-for="(item, index) in komponen" :key="index">
                <tr :class="index === 0 ? 'bg-yellow-100 font-semibold' : ''">
                    <td class="border border-gray-300 px-3 py-1 whitespace-nowrap">
                        <input type="text" x-model="komponen[index].component" disabled
                            class="w-auto min-w-0 inline text-sm bg-transparent border-none focus:outline-none" />
                    </td>
                    <td class="border border-gray-300 px-3 py-1 text-center whitespace-nowrap">
                        <input type="text" x-model="komponen[index].p_value"
                            class="w-auto min-w-0 inline text-sm text-center bg-transparent border-none focus:outline-none" />
                    </td>
                    <td class="border border-gray-300 px-3 py-1 text-center whitespace-nowrap">
                        <input type="text" x-model="komponen[index].l_value"
                            class="w-auto min-w-0 inline text-sm text-center bg-transparent border-none focus:outline-none" />
                    </td>
                    <td class="border border-gray-300 px-3 py-1 text-center whitespace-nowrap">
                        <input type="text" x-model="komponen[index].t_value"
                            class="w-auto min-w-0 inline text-sm text-center bg-transparent border-none focus:outline-none" />
                    </td>
                    <td class="border border-gray-300 px-3 py-1 text-center whitespace-nowrap">
                        <input type="text" x-model="komponen[index].qty"
                            class="w-auto min-w-0 inline text-sm text-center bg-transparent border-none focus:outline-none" />
                    </td>
                    <td class="border border-gray-300 px-3 py-1 text-center whitespace-nowrap">
                        <input type="text" x-model="komponen[index].qty"
                            class="w-auto min-w-0 inline text-sm text-center bg-transparent border-none focus:outline-none" />
                    </td>
                    <td class="border border-gray-300 px-3 py-1 text-center whitespace-nowrap">
                        <input type="text" x-model="komponen[index].qty"
                            class="w-auto min-w-0 inline text-sm text-center bg-transparent border-none focus:outline-none" />
                    </td>
                    <td class="border border-gray-300 px-3 py-1 text-center whitespace-nowrap">
                        <input type="text" x-model="komponen[index].qty"
                            class="w-auto min-w-0 inline text-sm text-center bg-transparent border-none focus:outline-none" />
                    </td>
                    <td class="border border-gray-300 px-3 py-1 text-center whitespace-nowrap">
                        <input type="text" x-model="komponen[index].qty"
                            class="w-auto min-w-0 inline text-sm text-center bg-transparent border-none focus:outline-none" />
                    </td>
                    <td class="border border-gray-300 px-3 py-1 text-center whitespace-nowrap">
                        <input type="text" x-model="komponen[index].qty"
                            class="w-auto min-w-0 inline text-sm text-center bg-transparent border-none focus:outline-none" />
                    </td>
                    <td class="border border-gray-300 px-3 py-1 text-center whitespace-nowrap">
                        <input type="text" x-model="komponen[index].qty"
                            class="w-auto min-w-0 inline text-sm text-center bg-transparent border-none focus:outline-none" />
                    </td>
                    <td class="border border-gray-300 px-3 py-1 text-center whitespace-nowrap">
                        <input type="text" x-model="komponen[index].qty"
                            class="w-auto min-w-0 inline text-sm text-center bg-transparent border-none focus:outline-none" />
                    </td>
                    <td class="border border-gray-300 px-3 py-1 text-center whitespace-nowrap">
                        <input type="text" x-model="komponen[index].qty"
                            class="w-auto min-w-0 inline text-sm text-center bg-transparent border-none focus:outline-none" />
                    </td>
                    <td class="border border-gray-300 px-3 py-1 text-center whitespace-nowrap">
                        <input type="text" x-model="komponen[index].qty"
                            class="w-auto min-w-0 inline text-sm text-center bg-transparent border-none focus:outline-none" />
                    </td>
                    <td class="border border-gray-300 px-3 py-1 text-center whitespace-nowrap">
                        <input type="text" x-model="komponen[index].qty"
                            class="w-auto min-w-0 inline text-sm text-center bg-transparent border-none focus:outline-none" />
                    </td>
                    <td class="border border-gray-300 px-3 py-1 text-center whitespace-nowrap">
                        <input type="text" x-model="komponen[index].qty"
                            class="w-auto min-w-0 inline text-sm text-center bg-transparent border-none focus:outline-none" />
                    </td>
                    <td class="border border-gray-300 px-3 py-1 text-center whitespace-nowrap">
                        <input type="text" x-model="komponen[index].qty"
                            class="w-auto min-w-0 inline text-sm text-center bg-transparent border-none focus:outline-none" />
                    </td>
                    <td class="border border-gray-300 px-3 py-1 text-center whitespace-nowrap">
                        <input type="text" x-model="komponen[index].qty"
                            class="w-auto min-w-0 inline text-sm text-center bg-transparent border-none focus:outline-none" />
                    </td>
                    <td class="border border-gray-300 px-3 py-1 text-center whitespace-nowrap">
                        <input type="text" x-model="komponen[index].qty"
                            class="w-auto min-w-0 inline text-sm text-center bg-transparent border-none focus:outline-none" />
                    </td>
                    <td class="border border-gray-300 px-3 py-1 text-center whitespace-nowrap">
                        <input type="text" x-model="komponen[index].qty"
                            class="w-auto min-w-0 inline text-sm text-center bg-transparent border-none focus:outline-none" />
                    </td>
                    <td class="border border-gray-300 px-3 py-1 text-center whitespace-nowrap">
                        <input type="text" x-model="komponen[index].qty"
                            class="w-auto min-w-0 inline text-sm text-center bg-transparent border-none focus:outline-none" />
                    </td>
                    <td class="border border-gray-300 px-3 py-1 text-center whitespace-nowrap">
                        <input type="text" x-model="komponen[index].qty"
                            class="w-auto min-w-0 inline text-sm text-center bg-transparent border-none focus:outline-none" />
                    </td>
                    <td class="border border-gray-300 px-3 py-1 text-center whitespace-nowrap">
                        <input type="text" x-model="komponen[index].qty"
                            class="w-auto min-w-0 inline text-sm text-center bg-transparent border-none focus:outline-none" />
                    </td>
                    <td class="border border-gray-300 px-3 py-1 text-center whitespace-nowrap">
                        <input type="text" x-model="komponen[index].qty"
                            class="w-auto min-w-0 inline text-sm text-center bg-transparent border-none focus:outline-none" />
                    </td>
                    <td class="border border-gray-300 px-3 py-1 text-center whitespace-nowrap">
                        <input type="text" x-model="komponen[index].qty"
                            class="w-auto min-w-0 inline text-sm text-center bg-transparent border-none focus:outline-none" />
                    </td>
                    <td class="border border-gray-300 px-3 py-1 text-center whitespace-nowrap">
                        <input type="text" x-model="komponen[index].qty"
                            class="w-auto min-w-0 inline text-sm text-center bg-transparent border-none focus:outline-none" />
                    </td>
                    <td class="border border-gray-300 px-3 py-1 text-center whitespace-nowrap">
                        <input type="text" x-model="komponen[index].qty"
                            class="w-auto min-w-0 inline text-sm text-center bg-transparent border-none focus:outline-none" />
                    </td>
                    <td class="border border-gray-300 px-3 py-1 text-center whitespace-nowrap">
                        <input type="text" x-model="komponen[index].qty"
                            class="w-auto min-w-0 inline text-sm text-center bg-transparent border-none focus:outline-none" />
                    </td>
                    <td class="border border-gray-300 px-3 py-1 text-center whitespace-nowrap">
                        <input type="text" x-model="komponen[index].qty"
                            class="w-auto min-w-0 inline text-sm text-center bg-transparent border-none focus:outline-none" />
                    </td>
                    <td class="border border-gray-300 px-3 py-1 text-center whitespace-nowrap">
                        <input type="text" x-model="komponen[index].qty"
                            class="w-auto min-w-0 inline text-sm text-center bg-transparent border-none focus:outline-none" />
                    </td>
                    <td class="border border-gray-300 px-3 py-1 text-center whitespace-nowrap">
                        <input type="text" x-model="komponen[index].qty"
                            class="w-auto min-w-0 inline text-sm text-center bg-transparent border-none focus:outline-none" />
                    </td>
                    <td class="border border-gray-300 px-3 py-1 text-center whitespace-nowrap">
                        <input type="text" x-model="komponen[index].qty"
                            class="w-auto min-w-0 inline text-sm text-center bg-transparent border-none focus:outline-none" />
                    </td>
                    <td class="border border-gray-300 px-3 py-1 text-center whitespace-nowrap">
                        <input type="text" x-model="komponen[index].qty"
                            class="w-auto min-w-0 inline text-sm text-center bg-transparent border-none focus:outline-none" />
                    </td>
                    <td class="border border-gray-300 px-3 py-1 text-center whitespace-nowrap">
                        <input type="text" x-model="komponen[index].qty"
                            class="w-auto min-w-0 inline text-sm text-center bg-transparent border-none focus:outline-none" />
                    </td>
                    <td class="border border-gray-300 px-3 py-1 text-center whitespace-nowrap">
                        <input type="text" x-model="komponen[index].qty"
                            class="w-auto min-w-0 inline text-sm text-center bg-transparent border-none focus:outline-none" />
                    </td>
                    <td class="border border-gray-300 px-3 py-1 text-center whitespace-nowrap">
                        <input type="text" x-model="komponen[index].qty"
                            class="w-auto min-w-0 inline text-sm text-center bg-transparent border-none focus:outline-none" />
                    </td>
                    <td class="border border-gray-300 px-3 py-1 text-center whitespace-nowrap">
                        <input type="text" x-model="komponen[index].qty"
                            class="w-auto min-w-0 inline text-sm text-center bg-transparent border-none focus:outline-none" />
                    </td>
                    <!-- Kolom lainnya bisa ditambahkan sesuai kebutuhan -->
                </tr>
            </template>
            <tr>
                <td colspan="5" class="px-3 py-2 text-center">
                    <button @click="komponen.push({ component: '', p_value: 0, l_value: 0, t_value: 0, qty: 0 })"
                            class="bg-blue-500 hover:bg-blue-600 text-black text-sm px-3 py-1 rounded">
                        + Tambah Komponen
                    </button>
                </td>
            </tr>
        </tbody>
    </table>
</div>
