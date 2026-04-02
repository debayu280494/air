
<div class="p-6 space-y-4">

    {{-- ================= FILTER ================= --}}
    <div class="flex flex-wrap gap-3 items-center bg-white p-4 rounded-lg shadow">

        <button wire:click="openModal"
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow">
            + Tambah
        </button>

        <input type="text"
            wire:model.live.debounce.300ms="search"
            class="border p-2 rounded"
            placeholder="Cari nama...">

        <select wire:model.live="filterGroup" class="border p-2 rounded">
            <option value="">Semua Grup</option>
            @foreach($groups as $g)
                <option value="{{ $g }}">{{ $g }}</option>
            @endforeach
        </select>

        <select wire:model.live="filterMonth" class="border p-2 rounded">
            <option value="">Semua Bulan</option>
            @foreach($this->months as $num => $name)
                <option value="{{ $num }}">{{ $name }}</option>
            @endforeach
        </select>

        <select wire:model.live="filterYear" class="border p-2 rounded">
            <option value="">Semua Tahun</option>
            @foreach($years as $y)
                <option value="{{ $y }}">{{ $y }}</option>
            @endforeach
        </select>

        <select wire:model.live="filterStatus" class="border p-2 rounded">
            <option value="">Semua Status</option>
            <option value="belum">Belum Bayar</option>
            <option value="lunas">Sudah Bayar</option>
        </select>

        <button wire:click="resetFilter"
            class="bg-gray-500 hover:bg-gray-600 text-white px-3 py-2 rounded">
            Reset
        </button>

    </div>

    

    {{-- ================= MODAL ================= --}}
    @if($isOpen)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-white p-6 rounded-xl shadow-xl w-full max-w-md">

                {{-- WARNING DUPLIKAT --}}
                @if($duplicateWarning)
                    <div class="bg-red-100 text-red-600 p-2 mb-2 text-sm rounded">
                        ⚠️ Data untuk bulan ini sudah ada!
                    </div>
                @endif

                {{-- CUSTOMER --}}
                <select wire:model.live="customer_id" class="w-full mb-2 border p-2 rounded">
                    <option value="">Pilih Customer</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>

                {{-- BULAN --}}
                <select wire:model.live="month" class="w-full mb-2 border p-2 rounded">
                    <option value="">Pilih Bulan</option>
                    @foreach($this->months as $num => $name)
                        <option value="{{ $num }}">{{ $name }}</option>
                    @endforeach
                </select>

                {{-- TAHUN --}}
                <select wire:model.live="year" class="w-full mb-2 border p-2 rounded">
                    <option value="">Pilih Tahun</option>
                    @foreach($years as $y)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endforeach
                </select>

                {{-- METER START --}}
                <input type="number"
                    wire:key="meter-start-{{ $customer_id }}-{{ $month }}-{{ $year }}"
                    wire:model.live="meter_start"
                    class="w-full mb-2 border p-2 rounded {{ $meterReadonly ? 'bg-gray-100' : '' }}"
                    @if($meterReadonly) readonly @endif
                    placeholder="Meter Awal">

                {{-- METER END --}}
                <input type="number"
                    wire:model.live="meter_end"
                    class="w-full mb-2 border p-2 rounded"
                    placeholder="Meter Akhir">

                @error('meter_end')
                    <div class="text-red-500 text-xs">{{ $message }}</div>
                @enderror

                @error('month')
                    <div class="text-red-500 text-xs">{{ $message }}</div>
                @enderror

                {{-- KETERANGAN --}}
                <input type="text"
                    wire:model.live="keterangan"
                    class="w-full border p-2 mb-2 rounded"
                    placeholder="Keterangan">

                {{-- BUTTON --}}
                <div class="flex gap-2 mt-3">
                    <button wire:click="save"
                        class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded"
                        @if($duplicateWarning) disabled @endif>
                        Simpan
                    </button>

                    <button wire:click="closeModal"
                        class="bg-gray-400 hover:bg-gray-500 px-3 py-1 rounded text-white">
                        Batal
                    </button>
                </div>

            </div>
        </div>
    @endif

    {{-- ================= ERROR DELETE ================= --}}
    @if($errors->has('delete'))
        <div class="bg-red-100 text-red-600 p-2 text-sm rounded">
            ⚠️ {{ $errors->first('delete') }}
        </div>
    @endif
    @if($search || $filterMonth || $filterYear || $filterStatus || $filterGroup)
        <div class="bg-blue-100 text-blue-700 px-3 py-2 rounded text-sm">
            🔎 Filter sedang aktif
        </div>
    @endif
    {{-- ================= TABLE ================= --}}
    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="w-full table-fixed border-collapse border border-gray-200 text-sm">

            {{-- HEADER --}}
            <thead class="bg-gray-100 sticky top-0 z-10">
                <tr class="text-left">
                    <th class="w-12 p-3 text-center">No</th>
                    <th class="w-48 p-3">Pelanggan</th>
                    <th class="w-32 p-3">Grup</th>
                    <th class="w-40 p-3">Bulan</th>
                    <th class="w-32 p-3 text-center">Meter</th>
                    <th class="w-24 p-3 text-center">Pemakaian</th>
                    <th class="w-32 p-3 text-right">Total</th>
                    <th class="w-32 p-3 text-center">Status</th>
                    <th class="w-28 p-3 text-center">Aksi</th>
                </tr>
            </thead>

            {{-- BODY --}}
            <tbody class="divide-y divide-gray-200">
                @forelse($usages as $i => $u)
                    <tr wire:key="usage-{{ $u->id }}" class="hover:bg-gray-50">

                        <td class="p-3 text-center">
                            {{ $usages->firstItem() + $i }}
                        </td>

                        <td class="p-3 truncate">
                            {{ $u->customer->name ?? '-' }}
                        </td>

                        <td class="p-3 truncate">
                            {{ $u->customer->group_name ?? '-' }}
                        </td>

                        <td class="p-3">
                            {{ \Carbon\Carbon::create()->month($u->month)->locale('id')->translatedFormat('F') }}
                            {{ $u->year }}
                        </td>

                        <td class="p-3 text-center">
                            {{ $u->meter_start }} - {{ $u->meter_end }}
                        </td>

                        <td class="p-3 text-center">
                            {{ $u->usage }}
                        </td>

                        <td class="p-3 text-right">
                            Rp {{ number_format($u->total_bill) }}
                        </td>

                        <td class="p-3 text-center">
                            @if($u->bill && $u->bill->status == 'lunas')
                                <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs">
                                    Lunas
                                </span>
                            @else
                                <span class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs">
                                    Belum
                                </span>
                            @endif
                        </td>

                        <td class="p-3 text-center">
                            <div class="flex justify-center gap-2">

                                @if(in_array($u->id, $this->lastUsageIds) && (!$u->bill || $u->bill->status !== 'lunas'))

                                    <button wire:click="edit({{ $u->id }})"
                                        class="bg-yellow-500 hover:bg-yellow-600 text-white px-2 py-1 rounded text-xs">
                                        Edit
                                    </button>

                                    <button wire:click="delete({{ $u->id }})"
                                        class="bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded text-xs">
                                        Hapus
                                    </button>

                                @endif

                            </div>
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center p-6 text-gray-500">
                            Data tidak ditemukan
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ================= PAGINATION ================= --}}
    <div class="mt-4">
        {{ $usages->links() }}
    </div>

</div>