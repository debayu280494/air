<div class="p-6 space-y-4">

    <!-- ================= TITLE ================= -->
    <h2 class="text-xl font-bold">Kelola Layanan</h2>

    <!-- ================= TOAST ================= -->
    <div
        x-data="{ show: false, message: '', type: '' }"
        x-on:toast.window="
            show = true;
            message = $event.detail.message;
            type = $event.detail.type;
            setTimeout(() => show = false, 3000)
        "
        x-show="show"
        x-transition
        class="fixed top-5 right-5 px-4 py-2 rounded text-white shadow z-50"
        :class="{
            'bg-green-500': type === 'success',
            'bg-red-500': type === 'error'
        }"
    >
        <span x-text="message"></span>
    </div>

    <!-- ================= BUTTON ================= -->
    <div class="flex justify-end">
        <button wire:click="openModal"
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow">
            + Tambah Layanan
        </button>
    </div>

    <!-- ================= LOADING ================= -->
    <div wire:loading class="flex items-center gap-2 text-sm text-gray-600">
        <div class="w-4 h-4 border-2 border-gray-400 border-t-transparent rounded-full animate-spin"></div>
        Loading...
    </div>

    <!-- ================= TABLE ================= -->
    <div class="overflow-x-auto bg-white shadow rounded-lg">

        <table class="min-w-full text-sm text-left">

            <thead class="bg-gray-100 text-gray-700 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3">No</th>
                    <th class="px-4 py-3">Nama</th>
                    <th class="px-4 py-3">Tarif</th>
                    <th class="px-4 py-3">Perawatan</th>
                    <th class="px-4 py-3 text-center">Aksi</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200">

                @forelse($services as $i => $s)
                <tr wire:key="service-{{ $s->id }}"
                    class="hover:bg-gray-50 transition">

                    <td class="px-4 py-3">
                        {{ $i + 1 }}
                    </td>

                    <td class="px-4 py-3 font-medium text-gray-900">
                        {{ $s->name }}
                    </td>

                    <td class="px-4 py-3">
                        Rp {{ number_format($s->price_per_meter,0,',','.') }}
                    </td>

                    <td class="px-4 py-3">
                        Rp {{ number_format($s->maintenance_fee,0,',','.') }}
                    </td>

                    <td class="px-4 py-3">
                        <div class="flex justify-center gap-2">

                            <button wire:click="edit({{ $s->id }})"
                                class="bg-yellow-500 hover:bg-yellow-600 text-white text-xs px-3 py-1 rounded">
                                Edit
                            </button>

                            <button wire:click="confirmDelete({{ $s->id }})"
                                class="bg-red-500 hover:bg-red-600 text-white text-xs px-3 py-1 rounded">
                                Hapus
                            </button>

                        </div>
                    </td>

                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-6 text-gray-500">
                        Data kosong
                    </td>
                </tr>
                @endforelse

            </tbody>

        </table>

    </div>

    <!-- ================= MODAL ================= -->
    @if($isOpen)
    <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">

        <div class="bg-white p-6 rounded-lg shadow-xl w-96">

            <h3 class="text-lg font-bold mb-4">
                {{ $editId ? 'Edit' : 'Tambah' }} Layanan
            </h3>

            <input type="text" wire:model="name"
                placeholder="Nama"
                class="w-full border p-2 mb-2 rounded focus:ring focus:ring-blue-200">

            <input type="number" wire:model="price_per_meter"
                placeholder="Tarif"
                class="w-full border p-2 mb-2 rounded focus:ring focus:ring-blue-200">

            <input type="number" wire:model="maintenance_fee"
                placeholder="Perawatan"
                class="w-full border p-2 mb-4 rounded focus:ring focus:ring-blue-200">

            <div class="flex justify-end gap-2">

                <button wire:click="closeModal"
                    class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                    Batal
                </button>

                <button wire:click="save"
                    wire:loading.attr="disabled"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                    Simpan
                </button>

            </div>

        </div>

    </div>
    @endif

</div>