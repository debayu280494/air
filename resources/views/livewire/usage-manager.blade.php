<div class="p-6">

    <button wire:click="openModal" class="bg-blue-500 text-white px-3 py-1">
        + Tambah
    </button>

    @if($isOpen)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
            <div class="bg-white p-4 w-96">

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
                <select wire:model.live="month" class="w-full mb-2">
                    <option value="">Bulan</option>
                    @for($i=1;$i<=12;$i++)
                        <option value="{{ $i }}">{{ $i }}</option>
                    @endfor
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

                    <button wire:click="$set('isOpen', false)" class="bg-gray-400 px-3 py-1">
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
    <table class="w-full mt-4 border">
        <thead>
            <tr>
                <th>No</th>
                <th>Pelanggan</th>
                <th>Bulan</th>
                <th>Meter</th>
                <th>Pemakaian</th>
                <th>Total</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($usages as $i => $u)
                <tr>
                    <td>{{ $i+1 }}</td>
                    <td>{{ $u->customer->name }}</td>
                    <td>{{ $u->month }}/{{ $u->year }}</td>
                    <td>{{ $u->meter_start }} - {{ $u->meter_end }}</td>
                    <td>{{ $u->usage }}</td>
                    <td>Rp {{ number_format($u->total_bill) }}</td>
                    <td>
                        <button wire:click="delete({{ $u->id }})">
                            Hapus
                        </button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

</div>