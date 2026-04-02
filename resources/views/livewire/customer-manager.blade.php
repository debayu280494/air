<div class="p-6 space-y-4">

    <!-- ================= FILTER ================= -->
    <div class="flex flex-wrap gap-3 items-center bg-white p-4 rounded-lg shadow">
        <button wire:click="openModal"
            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded shadow">
            + Tambah
        </button>
        <input type="text"
            wire:model.live.debounce.300ms="search"
            placeholder="Cari nama..."
            class="border px-3 py-2 rounded w-64 focus:ring focus:ring-blue-200">

        <input type="text"
            wire:model.live.debounce.300ms="filterGroup"
            placeholder="Filter grup..."
            class="border px-3 py-2 rounded w-48 focus:ring focus:ring-blue-200">

        <select wire:model.live="filterStatus"
            class="border px-3 py-2 rounded focus:ring focus:ring-blue-200">
            <option value="">Semua Status</option>
            <option value="aktif">Aktif</option>
            <option value="nonaktif">Nonaktif</option>
        </select>

        <button wire:click="$set('filterGroup', '')"
            class="text-sm text-gray-500 hover:text-black">
            Reset Grup
        </button>
    </div>

    <!-- ================= LOADING ================= -->
    <div wire:loading class="text-blue-500 text-sm">
        Loading...
    </div>

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

    <!-- ================= TABLE ================= -->
    <div class="overflow-x-auto bg-white shadow-lg rounded-xl border">

        <table class="w-full table-fixed text-sm text-gray-700">

            <thead class="bg-gray-100 text-xs uppercase sticky top-0 z-10">
                <tr>
                    <th class="w-16 px-4 py-3 text-center">No</th>
                    <th class="w-40 px-4 py-3 text-left">Nama</th>
                    <th class="w-56 px-4 py-3 text-left">Alamat</th>
                    <th class="w-32 px-4 py-3 text-center">Telepon</th>
                    <th class="w-32 px-4 py-3 text-center">Grup</th>
                    <th class="w-24 px-4 py-3 text-center">Status</th>
                    <th class="w-40 px-4 py-3 text-left">Layanan</th>
                    <th class="w-32 px-4 py-3 text-center">Aksi</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100">

                @forelse($customers as $index => $c)
                    <tr wire:key="customer-{{ $c->id }}" class="hover:bg-blue-50 even:bg-gray-50">

                        <td class="px-4 py-3 text-center">
                            {{ $customers->firstItem() + $index }}
                        </td>

                        <td class="px-4 py-3 font-medium">
                            {{ $c->name }}
                        </td>

                        <td class="px-4 py-3 truncate max-w-[200px]">
                            {{ $c->address ?? '-' }}
                        </td>

                        <td class="px-4 py-3 text-center">
                            {{ $c->phone ?? '-' }}
                        </td>

                        <td class="px-4 py-3 text-center">
                            {{ $c->group_name ?? '-' }}
                        </td>

                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-1 text-xs rounded-full
                                {{ $c->status === 'aktif'
                                    ? 'bg-green-100 text-green-700'
                                    : 'bg-red-100 text-red-700' }}">
                                {{ ucfirst($c->status) }}
                            </span>
                        </td>

                        <td class="px-4 py-3">
                            {{ optional($c->service)->name ?? '-' }}
                        </td>

                        <td class="px-4 py-3 text-center flex justify-center gap-2">

                            <button wire:click="edit({{ $c->id }})"
                                class="bg-yellow-500 hover:bg-yellow-600 text-white text-xs px-3 py-1 rounded">
                                Edit
                            </button>

                            <button wire:click="confirmDelete({{ $c->id }})"
                                class="bg-red-500 hover:bg-red-600 text-white text-xs px-3 py-1 rounded">
                                Hapus
                            </button>

                        </td>

                    </tr>
                    @empty
                <tr>
                    <td colspan="8" class="text-center py-6 text-gray-500">
                        Data tidak ditemukan
                    </td>
                </tr>
                @endforelse

            </tbody>

        </table>

    </div>

    <!-- ================= PAGINATION ================= -->
    <div class="mt-4">
        {{ $customers->links('pagination::tailwind') }}
    </div>

    <!-- ================= MODAL ================= -->
    @if($isOpen)
    <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">

        <div class="bg-white p-6 rounded-lg shadow-xl w-96">

            <h2 class="text-lg font-bold mb-4">
                {{ $editId ? 'Edit' : 'Tambah' }} Customer
            </h2>

            <input wire:model="name" class="w-full border p-2 mb-2 rounded" placeholder="Nama">
            <input wire:model="address" class="w-full border p-2 mb-2 rounded" placeholder="Alamat">
            <input wire:model="phone" class="w-full border p-2 mb-2 rounded" placeholder="Telepon">

            <input wire:model="group_name"
                class="w-full border p-2 mb-2 rounded"
                placeholder="Ketik grup...">

            <select wire:model="status" class="w-full border p-2 mb-2 rounded">
                <option value="aktif">Aktif</option>
                <option value="nonaktif">Nonaktif</option>
            </select>

            <select wire:model="service_id" class="w-full border p-2 mb-2 rounded">
                <option value="">Pilih Layanan</option>
                @foreach($services as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>

            <div class="flex justify-end gap-2 mt-4">

                <button wire:click="closeModal"
                    class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                    Batal
                </button>

                <button wire:click="save" wire:loading.attr="disabled"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                    Simpan
                </button>

            </div>

        </div>

    </div>
    @endif

</div>