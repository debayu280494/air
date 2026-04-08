<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Pemasukan Air</title>

    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
        }

        h2, h3 {
            margin: 5px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            border: 1px solid #000;
            padding: 6px;
        }

        th {
            background: #eee;
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .no-border td {
            border: none;
        }
    </style>
</head>
<body>

@php
    use Carbon\Carbon;

    $start = $request->start_date
        ? Carbon::parse($request->start_date)->translatedFormat('d F Y')
        : '-';

    $end = $request->end_date
        ? Carbon::parse($request->end_date)->translatedFormat('d F Y')
        : '-';

    $methodLabels = [
        'cash' => 'Cash',
        'transfer' => 'Transfer',
        'netzme' => 'QRIS Netzme',
        'qris' => 'QRIS BRI',
    ];
@endphp

{{-- ================= HEADER ================= --}}
<table class="no-border">
    <tr>
        <td width="80">
            <img src="{{ public_path('logo.png') }}" width="70">
        </td>
        <td>
            <h2 style="margin:0;">LAPORAN TAGIHAN AIR</h2>
            <p style="margin:0;">Periode: {{ $start }} s/d {{ $end }}</p>
            <p style="margin:0;">Dicetak: {{ \Carbon\Carbon::now()->translatedFormat('d F Y H:i') }}</p>
        </td>
    </tr>
</table>

<hr>

{{-- ================= DATA ================= --}}
@foreach($grouped as $group => $items)

    <h3>Grup: {{ $group }}</h3>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Customer</th>
                <th>Periode</th>
                <th>Total</th>
                <th>Metode</th>
                <th>Tanggal Bayar</th>
            </tr>
        </thead>
        <tbody>

        @php 
            $no = 1;
            $groupTotal = 0;
        @endphp

        @foreach($items as $bill)
            @php $groupTotal += $bill->total_bill; @endphp

            <tr>
                <td class="text-center">{{ $no++ }}</td>

                <td>
                    {{ optional($bill->customer)->name }}
                </td>

                <td class="text-center">
                    {{ Carbon::create()->month($bill->month)->translatedFormat('F') }}
                    {{ $bill->year }}
                </td>

                <td class="text-right">
                    Rp {{ number_format($bill->total_bill, 0, ',', '.') }}
                </td>

                <td class="text-center">
                    {{ $methodLabels[$bill->payment_method] ?? strtoupper($bill->payment_method) }}
                </td>

                <td class="text-center">
                    {{ Carbon::parse($bill->paid_at)->translatedFormat('d-m-Y') }}
                </td>
            </tr>
        @endforeach

        {{-- TOTAL PER GRUP --}}
        <tr>
            <td colspan="3"><strong>Total Grup</strong></td>
            <td class="text-right">
                <strong>Rp {{ number_format($groupTotal, 0, ',', '.') }}</strong>
            </td>
            <td colspan="2"></td>
        </tr>

        </tbody>
    </table>

@endforeach

{{-- ================= TOTAL PER METODE ================= --}}
<h3>Total per Metode Pembayaran</h3>

<table>
    <thead>
        <tr>
            <th>Metode</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>

        @php $totalMetode = 0; @endphp

        @foreach($methodTotals as $method => $total)
            @php $totalMetode += $total; @endphp

            <tr>
                <td>
                    {{ $methodLabels[$method] ?? strtoupper($method) }}
                </td>
                <td class="text-right">
                    Rp {{ number_format($total, 0, ',', '.') }}
                </td>
            </tr>
        @endforeach

        <tr>
            <td><strong>Total Semua Metode</strong></td>
            <td class="text-right">
                <strong>Rp {{ number_format($totalMetode, 0, ',', '.') }}</strong>
            </td>
        </tr>

    </tbody>
</table>

{{-- ================= GRAND TOTAL ================= --}}
<h3>
    Grand Total: Rp {{ number_format($grandTotal, 0, ',', '.') }}
</h3>

</body>
</html>