<div class="p-6 space-y-6">

    <!-- FILTER -->
    <div class="flex gap-3 items-center">
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

        <button onclick="exportPdf()" class="bg-green-600 text-white px-4 py-2 rounded">
            Export PDF
        </button>
    </div>

    <!-- SUMMARY -->
    <div class="grid md:grid-cols-3 gap-4">
        <div class="bg-blue-500 text-white p-4 rounded-xl shadow">
            <p>Total</p>
            <p class="text-xl font-bold">Rp {{ number_format($total,0,',','.') }}</p>
        </div>

        <div class="bg-red-500 text-white p-4 rounded-xl shadow">
            <p>Belum</p>
            <p class="text-xl font-bold">Rp {{ number_format($belum,0,',','.') }}</p>
        </div>

        <div class="bg-green-500 text-white p-4 rounded-xl shadow">
            <p>Lunas</p>
            <p class="text-xl font-bold">Rp {{ number_format($lunas,0,',','.') }}</p>
        </div>
    </div>

    <!-- PIE -->
    <div class="bg-white p-4 rounded shadow">
        <h2 class="font-bold mb-3">Pie Chart Customer</h2>
        <div class="flex justify-center">
            <div class="w-[500px] h-[400px]">
                <canvas id="pieChart"></canvas>
            </div>
        </div>
    </div>

    <!-- BAR TAGIHAN -->
    <div class="bg-white p-4 rounded shadow">
        <h2 class="font-bold mb-3">Grafik Tagihan Bulanan</h2>
        <div class="w-full h-[300px]">
            <canvas id="barChart"></canvas>
        </div>
    </div>

    <!-- BAR PEMASUKAN -->
    <div class="bg-white p-4 rounded shadow">
        <h2 class="font-bold mb-3">Grafik Revenue (Paid At)</h2>
        <div class="w-full h-[300px]">
            <canvas id="incomeChart"></canvas>
        </div>
    </div>

    <!-- GROUP -->
    <div class="bg-white p-4 rounded shadow">
        <h2 class="font-bold mb-4">Statistik per Group</h2>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($groupCustomers as $g)
                <div class="p-4 border rounded shadow space-y-2">

                    <div class="flex justify-between">
                        <h3 class="font-bold">{{ $g->group_name ?? '-' }}</h3>
                        <span class="text-sm text-gray-500">
                            {{ $g->total_customer }} Cust
                        </span>
                    </div>

                    <div class="text-sm">
                        <p>Total Invoice: {{ $g->total_invoice }}</p>
                        <p class="text-green-600">Lunas: {{ $g->invoice_lunas }}</p>
                        <p class="text-red-500">Belum: {{ $g->invoice_belum }}</p>
                    </div>

                    <div class="border-t pt-2 text-sm">
                        <p>Total: Rp {{ number_format($g->total_semua,0,',','.') }}</p>
                    </div>

                </div>
            @endforeach
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
let pieChart, barChart, incomeChart;

document.addEventListener('livewire:init', () => {

    Livewire.on('updateCharts', (event) => {
        const data = event[0];

        // ================= PIE =================
        if (pieChart) pieChart.destroy();
        pieChart = new Chart(document.getElementById('pieChart'), {
            type: 'pie',
            data: {
                labels: data.pieLabels,
                datasets: [{
                    data: data.pieData
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'right' }
                }
            }
        });

        // ================= TAGIHAN =================
        if (barChart) barChart.destroy();
        barChart = new Chart(document.getElementById('barChart'), {
            type: 'bar',
            data: {
                labels: data.barLabels,
                datasets: [{
                    label: 'Tagihan',
                    data: data.barData
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });

        // ================= PEMASUKAN =================
        if (incomeChart) incomeChart.destroy();
        incomeChart = new Chart(document.getElementById('incomeChart'), {
            type: 'bar',
            data: {
                labels: data.incomeLabels,
                datasets: [{
                    label: 'Pemasukan',
                    data: data.incomeData
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });

    });

});

// EXPORT
function exportPdf() {
    const pieImage = pieChart.toBase64Image();
    const barImage = barChart.toBase64Image();
    const incomeImage = incomeChart.toBase64Image(); // 🔥 TAMBAH INI

    Livewire.dispatch('exportPdfWithCharts', {
        pie: pieImage,
        bar: barImage,
        income: incomeImage // 🔥 TAMBAH INI
    });
}
</script>