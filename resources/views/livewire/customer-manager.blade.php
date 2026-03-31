<div class="p-6">

    <h2 class="text-xl font-bold mb-4">Kelola Pelanggan</h2>

    <button wire:click="openModal" class="bg-blue-500 text-white px-4 py-2 mb-4">
        + Tambah Pelanggan
    </button>

    <table class="w-full border border-gray-300">
        <thead class="bg-gray-100">
            <tr>
                <th class="p-2">No</th>
                <th class="p-2">ID</th>
                <th class="p-2">Nama</th>
                <th class="p-2">Alamat</th>
                <th class="p-2">Grup</th>
                <th class="p-2">No HP</th>
                <th class="p-2">Status</th>
                <th class="p-2">Layanan</th>
                <th class="p-2">Aksi</th>
            </tr>
        </thead>

        <tbody>
            @foreach($customers as $i => $c)
            <tr class="border-t">
                <td class="p-2">{{ $i+1 }}</td>
                <td class="p-2">{{ $c->customer_code }}</td>
                <td class="p-2">{{ $c->name }}</td>
                <td class="p-2">{{ $c->address }}</td>
                <td class="p-2">{{ $c->group_name }}</td>
                <td class="p-2">{{ $c->phone }}</td>
                <td class="p-2">{{ $c->status }}</td>
                <td class="p-2">{{ $c->service->name ?? '-' }}</td>
                <td class="p-2">
                    <button wire:click="edit({{ $c->id }})" class="bg-yellow-400 px-2 py-1">Edit</button>
                    <button wire:click="delete({{ $c->id }})" class="bg-red-500 text-white px-2 py-1">Hapus</button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- MODAL --}}
    @if($isOpen)
    <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">

        <div class="bg-white p-6 w-96">

            <h3 class="text-lg font-bold mb-4">
                {{ $editId ? 'Edit' : 'Tambah' }} Pelanggan
            </h3>

            <input type="text" wire:model="name" placeholder="Nama" class="w-full border p-2 mb-2">
            <input type="text" wire:model="address" placeholder="Alamat" class="w-full border p-2 mb-2">
            <input type="text" wire:model="group_name" placeholder="Grup" class="w-full border p-2 mb-2">
            <input type="text" wire:model="phone" placeholder="No HP" class="w-full border p-2 mb-2">

            <select wire:model="service_id" class="w-full border p-2 mb-2">
                <option value="">-- Pilih Layanan --</option>
                @foreach($services as $s)
                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                @endforeach
            </select>

            <select wire:model="status" class="w-full border p-2 mb-4">
                <option value="aktif">Aktif</option>
                <option value="nonaktif">Nonaktif</option>
            </select>

            <div class="flex justify-end gap-2">
                <button wire:click="closeModal" class="px-3 py-1 border">
                    Batal
                </button>

                <button wire:click="save" class="bg-blue-500 text-white px-3 py-1">
                    {{ $editId ? 'Update' : 'Simpan' }}
                </button>
            </div>

        </div>
    </div>
    @endif

</div>