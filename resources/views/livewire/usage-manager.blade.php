<div class="p-6">

    

    <div class="flex flex-wrap gap-3 mb-4">
        <button wire:click="openModal"
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow">
            + Tambah
        </button>
        <input type="text"
            wire:model.live.debounce.500ms="search"
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
            @foreach([
                1 => 'Januari',
                2 => 'Februari',
                3 => 'Maret',
                4 => 'April',
                5 => 'Mei',
                6 => 'Juni',
                7 => 'Juli',
                8 => 'Agustus',
                9 => 'September',
                10 => 'Oktober',
                11 => 'November',
                12 => 'Desember'
            ] as $num => $name)
                <option value="{{ $num }}">{{ $name }}</option>
            @endforeach
        </select>

        <select wire:model.live="filterYear" class="border p-2 rounded">
            <option value="">Tahun</option>
            @foreach($years as $y)
                <option value="{{ $y }}">{{ $y }}</option>
            @endforeach
        </select>

        <select wire:model.live="filterStatus" class="border p-2 rounded">
            <option value="">Semua Status</option>
            <option value="belum">Belum Bayar</option>
            <option value="lunas">Sudah Bayar</option>
        </select>

    </div>
    @if($isOpen)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
            <div class="bg-white p-6 rounded-xl shadow-xl w-96">

                {{-- WARNING DUPLIKAT --}}
                @if($duplicateWarning)
                    <div class="bg-red-100 text-red-600 p-2 mb-2 text-sm">
                        ⚠️ Data bulan ini sudah ada!
                    </div>
                @endif

                {{-- DEBUG --}}
                <div class="text-xs text-red-500 mb-2">
                    Customer: {{ $customer_id }} <br>
                    Meter Start: {{ $meter_start }} <br>
                    Meter End: {{ $meter_end }} <br>
                    Usage: {{ $usagePreview }}
                </div>

                {{-- CUSTOMER --}}
                <select wire:model.live="customer_id" class="w-full mb-2">
                    <option value="">Pilih Customer</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>

                {{-- MONTH --}}
                @php
                $months = [
                    1 => 'Januari',
                    2 => 'Februari',
                    3 => 'Maret',
                    4 => 'April',
                    5 => 'Mei',
                    6 => 'Juni',
                    7 => 'Juli',
                    8 => 'Agustus',
                    9 => 'September',
                    10 => 'Oktober',
                    11 => 'November',
                    12 => 'Desember'
                ];
                @endphp

                <select wire:model.live="month" class="w-full mb-2">
                    <option value="">Bulan</option>
                    @foreach($months as $num => $name)
                        <option value="{{ $num }}">{{ $name }}</option>
                    @endforeach
                </select>

                {{-- YEAR --}}
                <select wire:model.live="year" class="w-full mb-2">
                    <option value="">Pilih Tahun</option>
                    @foreach($years as $y)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endforeach
                </select>

                {{-- METER START --}}
                <input 
                    type="number" 
                    value="{{ $meter_start }}" 
                    readonly 
                    class="w-full mb-2 bg-gray-100"
                >

                {{-- METER END --}}
                <input 
                    type="number" 
                    wire:model.live="meter_end" 
                    class="w-full mb-2"
                >

                @error('meter_end')
                    <div class="text-red-500 text-xs">{{ $message }}</div>
                @enderror

                @error('month')
                    <div class="text-red-500 text-xs">{{ $message }}</div>
                @enderror

                {{-- KETERANGAN --}}
                <input 
                    type="text" 
                    wire:model.live="keterangan" 
                    class="w-full border p-1 mb-2" 
                    placeholder="Keterangan"
                >

                <div class="flex gap-2">
                    <button 
                        wire:click="save" 
                        class="bg-green-500 text-white px-3 py-1"
                        @if($duplicateWarning) disabled @endif
                    >
                        Simpan
                    </button>

                    <button wire:click="closeModal" class="bg-gray-400 px-3 py-1">
                        Batal
                    </button>
                </div>

            </div>
        </div>
    @endif

    @if($errors->has('delete'))
        <div class="bg-red-100 text-red-600 p-2 mb-2 text-sm">
            ⚠️ {{ $errors->first('delete') }}
        </div>
    @endif

    {{-- TABLE --}}
    <div class="overflow-x-auto mt-4">
        <table class="w-full border border-gray-300 rounded-lg overflow-hidden">
            <thead class="bg-gray-200">
            <tr>
                <th class="p-2">No</th>
                <th class="p-2">Pelanggan</th>
                <th class="p-2">Grup</th>
                <th class="p-2">Bulan</th>
                <th class="p-2">Meter</th>
                <th class="p-2">Pemakaian</th>
                <th class="p-2">Total</th>
                <th class="p-2">Status</th>
                <th class="p-2">Aksi</th>
            </tr>
            </thead>
        <tbody>
            @foreach($usages as $i => $u)
            <tr wire:key="usage-{{ $u->id }}" class="border-t hover:bg-gray-50">
                <td class="p-2">{{ $usages->firstItem() + $i }}</td>
                <td class="p-2">{{ $u->customer->name }}</td>
                <td class="p-2">
                    {{ $u->customer->group_name ?? '-' }}
                </td>
                <td class="p-2">
                {{ \Carbon\Carbon::create()->month($u->month)->locale('id')->translatedFormat('F') }} {{ $u->year }}
                </td>
                <td class="p-2">{{ $u->meter_start }} - {{ $u->meter_end }}</td>
                <td class="p-2">{{ $u->usage }}</td>
                <td class="p-2">Rp {{ number_format($u->total_bill) }}</td>
                <td class="p-2">
                    @if($u->bill && $u->bill->status == 'lunas')
                        <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs">
                            Sudah Bayar
                        </span>
                    @else
                        <span class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs">
                            Belum Bayar
                        </span>
                    @endif
                </td>
                <td class="p-2 flex gap-2">

            @if($u->is_last && (!$u->bill || $u->bill->status !== 'lunas'))
                <button wire:click="edit({{ $u->id }})"
                class="bg-yellow-500 hover:bg-yellow-600 text-white px-2 py-1 rounded">
                Edit
                </button>

                @if($u->is_last && (!$u->bill || $u->bill->status !== 'lunas'))
                    <button wire:click="delete({{ $u->id }})"
                        class="bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded">
                        Hapus
                    </button>
                @endif
            @endif

            </td>
            </tr>
            @endforeach
        </tbody>
        </table>
    </div>
    
</div>