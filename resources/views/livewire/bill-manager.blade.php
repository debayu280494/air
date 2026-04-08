<div>

    {{-- FILTER + TABLE --}}
    <div class="p-6 space-y-4">

        <!-- FILTER -->
        <div class="bg-white p-4 rounded-lg shadow">
            <h3 class="font-bold mb-2">Filter Data</h3>

            <div class="flex flex-wrap gap-3">

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

                <select wire:model.live="filterStatus" class="border p-2 rounded">
                    <option value="">Semua Status</option>
                    <option value="belum">Belum Bayar</option>
                    <option value="lunas">Lunas</option>
                </select>

            </div>
        </div>

        <div class="bg-red-50 p-4 rounded-lg shadow mt-4 border border-red-200">
            <h3 class="font-bold mb-2 text-red-600">Export PDF</h3>

            <div class="flex flex-wrap gap-3 items-center">

                <div>
                    <label class="text-xs text-gray-500">Tanggal Mulai</label>
                    <input type="date" wire:model="exportStartDate" class="border p-2 rounded">
                </div>

                <div>
                    <label class="text-xs text-gray-500">Tanggal Akhir</label>
                    <input type="date" wire:model="exportEndDate" class="border p-2 rounded">
                </div>

                <div>
                    <label class="text-xs text-gray-500">Grup</label>
                    <select wire:model="exportGroup" class="border p-2 rounded">
                        <option value="">Semua Grup</option>
                        @foreach($groups as $g)
                            <option value="{{ $g }}">{{ $g }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- BUTTON EXPORT --}}
                <button wire:click="exportPdf"
                    class="bg-red-600 text-white px-4 py-2 rounded mt-4">
                    Export PDF
                </button>

            </div>
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
                    <tr wire:key="bill-{{ $bill->id }}" class="border-t hover:bg-gray-50">

                        <td class="p-3">
                            {{ optional($bill->customer)->name ?? '-' }}
                        </td>

                        <td class="p-3 text-center">
                            {{ optional($bill->customer)->group_name ?? '-' }}
                        </td>

                        <td class="p-3 text-center">
                            {{ \Carbon\Carbon::create()->month($bill->month)->translatedFormat('F') }} {{ $bill->year }}
                        </td>

                        <td class="p-3 text-center font-semibold">
                            Rp {{ number_format($bill->total_bill, 0, ',', '.') }}
                        </td>

                        <td class="p-3 text-center">
                            @if($bill->status == 'lunas')
                                <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs">Lunas</span>
                            @else
                                <span class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs">Belum</span>
                            @endif
                        </td>

                        <td class="p-3 text-center text-xs">
                            @if($bill->status == 'lunas')
                                {{ $paymentMethods[$bill->payment_method] ?? '-' }} <br>
                                {{ $bill->paid_at }}
                            @else
                                -
                            @endif
                        </td>

                        <td class="p-3 flex gap-2 justify-center">

                            <a href="{{ route('invoice.show', $bill->id) }}"
                                class="bg-blue-500 text-white px-3 py-1 rounded text-xs">
                                Detail
                            </a>

                            <!-- <a href="{{ route('invoice.pdf', $bill->id) }}"
                                class="bg-gray-700 text-white px-3 py-1 rounded text-xs">
                                PDF
                            </a> -->

                            @if($bill->status == 'belum')
                                <button 
                                    wire:click="openPayment({{ $bill->id }})"
                                    wire:key="pay-{{ $bill->id }}"
                                    class="bg-green-500 text-white px-3 py-1 rounded text-xs"
                                    wire:loading.attr="disabled">
                                    Bayar
                                </button>
                            @endif

                            @if($bill->status == 'belum' && $bill->is_latest)
                                <button onclick="confirmDelete({{ $bill->id }})"
                                    class="bg-red-500 text-white px-3 py-1 rounded text-xs">
                                    Hapus
                                </button>
                            @endif

                        </td>

                    </tr>
                    @endforeach
                </tbody>
            </table>

        </div>

        <!-- PAGINATION -->
        {{ $bills->links() }}

    </div>

    <!-- MODAL -->
    @if($showPaymentModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
        <div class="bg-white p-6 rounded-xl w-96">

            <h2 class="text-lg font-bold mb-3">Pembayaran</h2>

            <select wire:model="payment_method" class="w-full border p-2 mb-2">
                @foreach($paymentMethods as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>

            <input type="date" wire:model="paid_at" class="w-full border p-2 mb-3">

            <div class="flex flex-col gap-2">
                <button wire:click="processPayment"
                    wire:loading.attr="disabled"
                    class="bg-green-500 text-white px-3 py-2 rounded w-full">

                    <span wire:loading.remove>Simpan</span>
                    <span wire:loading>Memproses...</span>
                </button>

                <button wire:click="closePaymentModal"
                    class="bg-gray-400 text-white px-3 py-2 rounded w-full">
                    Batal
                </button>
            </div>

        </div>
    </div>
    @endif

</div>

<!-- SCRIPT -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('livewire:init', () => {

    Livewire.on('notify', (event) => {
        let data = Array.isArray(event) ? event[0] : event;

        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: data.type ?? 'info',
            title: data.message ?? 'No message',
            showConfirmButton: false,
            timer: 2500,
            timerProgressBar: true
        });
    });

    Livewire.on('open-pdf', (event) => {
        let data = Array.isArray(event) ? event[0] : event;
        window.open(data.url, '_blank');
    });

});
</script>
<script>
// CONFIRM DELETE
window.confirmDelete = function(id) {
    Swal.fire({
        title: 'Yakin ingin menghapus?',
        text: "Data tidak bisa dikembalikan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Hapus!'
    }).then((result) => {
        if (result.isConfirmed) {
            @this.call('deleteBill', id);
        }
    });
}
</script>

