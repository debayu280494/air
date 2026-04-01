<div class="p-6 space-y-4">

    <!-- ================= FILTER ================= -->
    <div class="flex flex-wrap gap-3 items-center bg-white p-4 rounded-lg shadow">

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

    <!-- ================= BUTTON ================= -->
    <div class="flex justify-end">
        <button wire:click="openModal"
            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded shadow">
            + Tambah
        </button>
    </div>

    <!-- ================= TABLE ================= -->
    <div class="overflow-x-auto bg-white shadow rounded-lg">

        <table class="min-w-full text-sm text-left">

            <thead class="bg-gray-100 text-gray-700 uppercase text-xs sticky top-0">
                <tr>

                    <th class="px-4 py-3 cursor-pointer" wire:click="sortBy('name')">
                        Nama
                        @if($sortField === 'name')
                            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                        @endif
                    </th>

                    <th class="px-4 py-3">Alamat</th>
                    <th class="px-4 py-3">Telepon</th>
                    <th class="px-4 py-3">Grup</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Layanan</th>
                    <th class="px-4 py-3 text-center">Aksi</th>

                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200">

                @forelse($customers as $c)
                <tr wire:key="customer-{{ $c->id }}"
                    class="hover:bg-gray-50 transition">

                    <td class="px-4 py-3 font-medium text-gray-900">
                        {{ $c->name }}
                    </td>

                    <td class="px-4 py-3">
                        {{ $c->address ?? '-' }}
                    </td>

                    <td class="px-4 py-3">
                        {{ $c->phone ?? '-' }}
                    </td>

                    <td class="px-4 py-3">
                        {{ $c->group_name ?? '-' }}
                    </td>

                    <td class="px-4 py-3">
                        <span class="px-2 py-1 text-xs rounded 
                            {{ $c->status === 'aktif' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ ucfirst($c->status) }}
                        </span>
                    </td>

                    <td class="px-4 py-3">
                        {{ $c->service->name ?? '-' }}
                    </td>

                    <td class="px-4 py-3">
                        <div class="flex justify-center gap-2">

                            <button wire:click="edit({{ $c->id }})"
                                class="bg-yellow-500 hover:bg-yellow-600 text-white text-xs px-3 py-1 rounded">
                                Edit
                            </button>

                            <button wire:click="confirmDelete({{ $c->id }})"
                                class="bg-red-500 hover:bg-red-600 text-white text-xs px-3 py-1 rounded">
                                Hapus
                            </button>

                        </div>
                    </td>

                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-6 text-gray-500">
                        Data kosong
                    </td>
                </tr>
                @endforelse

            </tbody>

        </table>
    </div>

    <!-- ================= PAGINATION ================= -->
    <div class="mt-4">
        {{ $customers->links() }}
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
                @foreach($services as $s)
                    <option value="{{ $s->id }}">{{ $s->name }}</option>
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