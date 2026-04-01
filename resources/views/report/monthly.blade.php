<div class="p-6">
    <h1 class="text-xl font-bold mb-4">Laporan Bulanan {{ $year }}</h1>

    <a href="{{ route('report.monthly.pdf', ['year'=>$year]) }}"
        class="bg-red-500 text-white px-3 py-1 rounded">
        Export PDF
    </a>

    <table class="w-full mt-4 border">
        <tr class="bg-gray-200">
            <th>Bulan</th>
            <th>Total</th>
        </tr>

        @foreach($data as $d)
        <tr>
            <td>{{ \Carbon\Carbon::create()->month($d->bulan)->translatedFormat('F') }}</td>
            <td>Rp {{ number_format($d->total,0,',','.') }}</td>
        </tr>
        @endforeach
    </table>
</div>