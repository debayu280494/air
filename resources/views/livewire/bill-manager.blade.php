<div class="p-6 space-y-4">

    <!-- FILTER -->
    <div class="flex flex-wrap gap-3 items-center bg-white p-4 rounded-lg shadow">

        <input type="text"
            wire:model.live.debounce.300ms="search"
            placeholder="Cari customer..."
            class="border p-2 rounded w-64">

        <select wire:model.live="filterMonth" class="border p-2 rounded">
            <option value="">Semua Bulan</option>
            @for($i=1; $i<=12; $i++)
                <option value="{{ $i }}">
                    {{ \Carbon\Carbon::create()->month($i)->translatedFormat('F') }}
                </option>
            @endfor
        </select>

        <select wire:model.live="filterYear" class="border p-2 rounded">
            <option value="">Semua Tahun</option>
            @foreach(range(date('Y')-5, date('Y')+1) as $y)
                <option value="{{ $y }}">{{ $y }}</option>
            @endforeach
        </select>

        <select wire:model.live="filterGroup" class="border p-2 rounded">
            <option value="">Semua Grup</option>
            @foreach($groups as $g)
                <option value="{{ $g }}">{{ $g }}</option>
            @endforeach
        </select>

        <select wire:model.live="filterStatus"
            class="border p-2 rounded">
            <option value="">Semua Status</option>
            <option value="belum">Belum Bayar</option>
            <option value="lunas">Lunas</option>
        </select>

    </div>

    <!-- TABLE -->
    <div class="bg-white shadow-lg rounded-xl overflow-hidden">

        <table class="w-full text-sm">
            <thead class="bg-gray-100 text-gray-700">
                <tr>
                    <th class="p-3 text-left">Customer</th>
                    <th class="p-3">Grup</th>
                    <th class="p-3">Periode</th>
                    <th class="p-3">Total</th>
                    <th class="p-3">Status</th>
                    <th class="p-3">Pembayaran</th>
                    <th class="p-3">Aksi</th>
                </tr>
            </thead>

            <tbody>
                @foreach($bills as $bill)
                <tr class="border-t hover:bg-gray-50 transition">

                    <td class="p-3 font-medium">
                        {{ $bill->customer->name ?? '-' }}
                    </td>

                    <td class="p-3 text-center">
                        {{ $bill->customer->group_name ?? '-' }}
                    </td>

                    <td class="p-3 text-center">
                        {{ \Carbon\Carbon::create()->month($bill->month)->translatedFormat('F') }} {{ $bill->year }}
                    </td>

                    <td class="p-3 text-center font-semibold">
                        Rp {{ number_format($bill->total_bill, 0, ',', '.') }}
                    </td>

                    <td class="p-3 text-center">
                        @if($bill->status == 'lunas')
                            <span class="bg-green-100 text-green-700 px-2 py-1 rounded-full text-xs">
                                Lunas
                            </span>
                        @else
                            <span class="bg-red-100 text-red-700 px-2 py-1 rounded-full text-xs">
                                Belum
                            </span>
                        @endif
                    </td>

                    <td class="p-3 text-xs text-center">
                        @if($bill->status == 'lunas')
                            {{ strtoupper($bill->payment_method) }} <br>
                            {{ $bill->paid_at }}
                        @else
                            -
                        @endif
                    </td>

                    <td class="p-3 flex gap-2 justify-center">

                        <a href="{{ route('invoice.show', $bill->id) }}"
                            class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-xs shadow">
                            Invoice
                        </a>

                        <a href="{{ route('invoice.pdf', $bill->id) }}"
                            class="bg-gray-700 text-white px-3 py-1 rounded text-xs">
                            PDF
                        </a>

                        @if($bill->status == 'belum')
                            <button wire:click="openPayment({{ $bill->id }})"
                                class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-xs shadow">
                                Bayar
                            </button>
                        @endif

                        @if($bill->status == 'belum' && $bill->is_latest)
                            <button 
                                onclick="confirmDelete({{ $bill->id }})"
                                class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-xs">
                                Hapus
                            </button>
                        @endif

                    </td>

                </tr>
                @endforeach
            </tbody>
        </table>

    </div>

    <!-- MODAL PEMBAYARAN -->
    @if($showPaymentModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
        <div class="bg-white p-6 rounded-xl shadow-xl w-96">

            <h2 class="text-lg font-bold mb-3">Proses Pembayaran</h2>

            <div class="mb-2">
                <label>Metode Pembayaran</label>
                <select wire:model="payment_method" class="w-full border p-2 rounded">
                    <option value="cash">Cash</option>
                    <option value="transfer">Transfer</option>
                </select>
            </div>

            <div class="mb-2">
                <label>Tanggal Bayar</label>
                <input type="date" wire:model="paid_at" class="w-full border p-2 rounded">
            </div>

            <div class="flex gap-2 mt-4">
                <button wire:click="processPayment"
                    class="bg-green-500 text-white px-3 py-1 rounded">
                    Simpan
                </button>

                <button wire:click="$set('showPaymentModal', false)"
                    class="bg-gray-400 px-3 py-1 rounded">
                    Batal
                </button>
            </div>

        </div>
    </div>
    @endif

</div>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function confirmDelete(id) {
    Swal.fire({
        title: 'Yakin ingin menghapus?',
        text: "Data tidak bisa dikembalikan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            @this.call('deleteBill', id);
        }
    });
}
</script>