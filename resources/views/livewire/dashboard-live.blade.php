<div class="p-6 space-y-6">

    <!-- FILTER -->
    <div class="flex gap-3">
        <select wire:model.live="month" class="border p-2 rounded">
            <option value="">Semua Bulan</option>
            @php
                $months = [
                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
                    4 => 'April', 5 => 'Mei', 6 => 'Juni',
                    7 => 'Juli', 8 => 'Agustus', 9 => 'September',
                    10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                ];
            @endphp

            @foreach($months as $num => $name)
                <option value="{{ $num }}">{{ $name }}</option>
            @endforeach
        </select>

        <select wire:model.live="year" class="border p-2 rounded">
            @for($y=2022;$y<=now()->year;$y++)
                <option value="{{ $y }}">{{ $y }}</option>
            @endfor
        </select>

        <button wire:click="export"
            class="bg-green-600 text-white px-4 py-2 rounded">
            Export Excel
        </button>
    </div>
    <!-- SUMMARY -->
    <div class="grid md:grid-cols-3 gap-4">
        <div class="bg-blue-500 text-white p-4 rounded-xl shadow-lg">
            <p class="text-sm">Total</p>
            <p class="text-lg font-bold">
                Rp {{ number_format($total,0,',','.') }}
            </p>
        </div>

        <div class="bg-red-500 text-white p-4 rounded-xl shadow-lg">
            <p class="text-sm">Belum</p>
            <p class="text-lg font-bold">
                Rp {{ number_format($belum,0,',','.') }}
            </p>
        </div>

        <div class="bg-green-500 text-white p-4 rounded-xl shadow-lg">
            <p class="text-sm">Lunas</p>
            <p class="text-lg font-bold">
                Rp {{ number_format($lunas,0,',','.') }}
            </p>
        </div>
    </div>

    <!-- CHART -->
    <div class="bg-white p-4 rounded shadow">
        <h2 class="font-bold mb-3">Pie Chart Customer</h2>
        <div class="flex justify-center">
            <div class="w-64 h-64">
                <canvas id="pieChart"></canvas>
            </div>
        </div>
    </div>

    <!-- BAR CHART -->
    <div class="bg-white p-4 rounded shadow">
        <h2 class="font-bold mb-3">Grafik Pemasukan Bulanan</h2>
        <div class="w-full h-64">
            <canvas id="barChart"></canvas>
        </div>
    </div>

    <!-- GROUP -->
    <div class="bg-white p-4 rounded-xl shadow">
        <h2 class="font-bold mb-4">Statistik per Group</h2>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($groupCustomers as $g)
                <div class="bg-white rounded-xl shadow p-4 space-y-3 hover:shadow-xl transition duration-300">

                    <!-- HEADER -->
                    <div class="flex justify-between items-center">
                        <h3 class="font-bold text-lg">
                            {{ $g->group_name ?? '-' }}
                        </h3>
                        <span class="text-sm text-gray-500">
                            {{ $g->total_customer }} Customer
                        </span>
                    </div>

                    <!-- INVOICE -->
                    <div class="grid grid-cols-3 text-center text-sm">
                        <div>
                            <p class="text-gray-400">Total</p>
                            <p class="font-semibold">{{ $g->total_invoice }}</p>
                        </div>
                        <div>
                            <p class="text-green-500">Lunas</p>
                            <p class="font-semibold text-green-600">{{ $g->invoice_lunas }}</p>
                        </div>
                        <div>
                            <p class="text-red-500">Belum</p>
                            <p class="font-semibold text-red-600">{{ $g->invoice_belum }}</p>
                        </div>
                    </div>

                    <!-- KEUANGAN -->
                    <div class="space-y-1 text-sm border-t pt-2">

                        <div class="flex justify-between">
                            <span class="text-gray-500">Uang Masuk</span>
                            <span class="text-green-600 font-semibold">
                                Rp {{ number_format($g->total_lunas,0,',','.') }}
                            </span>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-gray-500">Piutang</span>
                            <span class="text-red-500 font-semibold">
                                Rp {{ number_format($g->total_belum,0,',','.') }}
                            </span>
                        </div>

                        <div class="flex justify-between border-t pt-1 font-bold">
                            <span>Total</span>
                            <span>
                                Rp {{ number_format($g->total_semua,0,',','.') }}
                            </span>
                        </div>

                    </div>

                </div>
            @endforeach
            </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    let pieChart;
    let barChart;

    document.addEventListener('livewire:init', () => {

        Livewire.on('updateCharts', (event) => {

            const data = event[0] ?? {}; // 🔥 WAJIB (fix Livewire v3)

            const pieLabels = data.pieLabels ?? [];
            const pieData   = data.pieData ?? [];
            const barLabels = data.barLabels ?? [];
            const barData   = data.barData ?? [];

            console.log('CHART DATA:', data);

            // PIE
            const pieCtx = document.getElementById('pieChart');

            if (pieChart) pieChart.destroy();

            pieChart = new Chart(pieCtx, {
                type: 'pie',
                data: {
                    labels: pieLabels,
                    datasets: [{
                        data: pieData,
                        backgroundColor: [
                            '#3b82f6',
                            '#22c55e',
                            '#ef4444',
                            '#f59e0b',
                            '#8b5cf6'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });

            // BAR
            const barCtx = document.getElementById('barChart');

            if (barChart) barChart.destroy();

            barChart = new Chart(barCtx, {
                type: 'bar',
                data: {
                    labels: barLabels.map(b => {
                        const bulan = [
                            'Jan','Feb','Mar','Apr','Mei','Jun',
                            'Jul','Agu','Sep','Okt','Nov','Des'
                        ];
                        return bulan[b-1];
                    }),
                    datasets: [{
                        label: 'Pemasukan',
                        data: barData
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });

        });

    });
</script>