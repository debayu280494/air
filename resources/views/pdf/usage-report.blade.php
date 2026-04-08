<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Tagihan Pemakaian</title>

    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width:100%; border-collapse: collapse; margin-top:10px; }
        th, td { border:1px solid #000; padding:6px; }
        th { background:#eee; }
        .header-table td { border:none; padding:2px; }
    </style>
</head>
<body>

@php
use Carbon\Carbon;

// ✅ PERBAIKAN PERIODE
$start = ($request->start_month && $request->start_year)
    ? Carbon::create($request->start_year, $request->start_month, 1)->translatedFormat('F Y')
    : '-';

$end = ($request->end_month && $request->end_year)
    ? Carbon::create($request->end_year, $request->end_month, 1)->translatedFormat('F Y')
    : '-';
@endphp

<h2 style="text-align:center;">LAPORAN TAGIHAN AIR</h2>

<hr>

<table class="header-table">
    <tr>
        <td>Periode</td>
        <td>: {{ $start }} s/d {{ $end }}</td>
    </tr>
    <tr>
        <td>Status</td>
        <td>: {{ $request->status ?? 'Semua' }}</td>
    </tr>
    <tr>
        <td>Grup</td>
        <td>: {{ $request->group ?? 'Semua' }}</td>
    </tr>
</table>

@foreach($grouped as $group => $items)

<h3>Grup: {{ $group }}</h3>

<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Customer</th>
            <th>Periode</th>
            <th>Meter</th>
            <th>Pemakaian</th>
            <th>Total</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>

    @php $no=1; $total=0; @endphp

    @foreach($items as $u)
        @php $total += $u->total_bill; @endphp

        <tr>
            <td>{{ $no++ }}</td>
            <td>{{ $u->customer->name ?? '-' }}</td>
            <td>
                {{ Carbon::create()->month($u->month)->translatedFormat('F') }} {{ $u->year }}
            </td>
            <td>{{ $u->meter_start }} - {{ $u->meter_end }}</td>
            <td>{{ $u->usage }}</td>
            <td>Rp {{ number_format($u->total_bill) }}</td>
            <td>{{ $u->bill->status ?? '-' }}</td>
        </tr>
    @endforeach

    <tr>
        <td colspan="5"><strong>Total Grup</strong></td>
        <td colspan="2"><strong>Rp {{ number_format($total) }}</strong></td>
    </tr>

    </tbody>
</table>

@endforeach

<h3>Grand Total: Rp {{ number_format($grandTotal) }}</h3>

</body>
</html>