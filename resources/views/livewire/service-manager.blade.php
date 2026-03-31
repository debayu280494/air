<div class="p-6">

    <h2 class="text-xl font-bold mb-4">Kelola Layanan</h2>

    <button wire:click="openModal" class="bg-blue-500 text-white px-4 py-2 mb-4">
        + Tambah Layanan
    </button>

    <table class="w-full border border-gray-300">
        <thead class="bg-gray-100">
            <tr>
                <th class="p-2">No</th>
                <th class="p-2">Nama Layanan</th>
                <th class="p-2">Tarif / Meter</th>
                <th class="p-2">Biaya Perawatan</th>
                <th class="p-2">Aksi</th>
            </tr>
        </thead>

        <tbody>
            @foreach($services as $i => $s)
            <tr class="border-t">
                <td class="p-2">{{ $i+1 }}</td>
                <td class="p-2">{{ $s->name }}</td>
                <td class="p-2">Rp {{ number_format($s->price_per_meter,0,',','.') }}</td>
                <td class="p-2">Rp {{ number_format($s->maintenance_fee,0,',','.') }}</td>
                <td class="p-2">
                    <button wire:click="edit({{ $s->id }})" class="bg-yellow-400 px-2 py-1">Edit</button>
                    <button wire:click="delete({{ $s->id }})" class="bg-red-500 text-white px-2 py-1">Hapus</button>
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
                {{ $editId ? 'Edit' : 'Tambah' }} Layanan
            </h3>

            <input type="text" wire:model="name" placeholder="Nama Layanan" class="w-full border p-2 mb-2">

            <input type="number" wire:model="price_per_meter" placeholder="Tarif per Meter" class="w-full border p-2 mb-2">

            <input type="number" wire:model="maintenance_fee" placeholder="Biaya Perawatan" class="w-full border p-2 mb-4">

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