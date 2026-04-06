<!DOCTYPE html>
<html>
<head>
    <title>Laporan Dashboard</title>
    <style>
        body { font-family: Arial; font-size: 12px; }

        .header { text-align: center; margin-bottom: 10px; }

        .logo { width: 60px; }

        .title { font-size: 16px; font-weight: bold; }

        .summary { margin: 10px 0; }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            border: 1px solid black;
            padding: 5px;
            text-align: center;
        }

        th { background: #eee; }

        .section { margin-top: 15px; }

        .footer {
            margin-top: 40px;
            text-align: right;
        }
    </style>
</head>

<body>

<div class="header">
    <img src="{{ public_path('logo.png') }}" class="logo">
    <div class="title">LAPORAN DASHBOARD</div>
    <div>Sistem Tagihan Air</div>
</div>

<div>
    <b>Tahun:</b> {{ $year }} |
    <b>Bulan:</b> {{ $month ?? 'Semua' }}
</div>

<div class="summary">
    <b>Total:</b> Rp {{ number_format($total) }} <br>
    <b>Lunas:</b> Rp {{ number_format($lunas) }} <br>
    <b>Belum:</b> Rp {{ number_format($belum) }}
</div>

<!-- CHART -->
<div class="section">
    @if($pieChart)
        <h4>Pie Customer</h4>
        <img src="{{ $pieChart }}" style="width:300px;">
    @endif
</div>

<div class="section">
    @if($barChart)
        <h4>Grafik Tagihan</h4>
        <img src="{{ $barChart }}" style="width:100%;">
    @endif
</div>

<div class="section">
    @if($incomeChart)
        <h4>Grafik Pemasukan (Paid At)</h4>
        <img src="{{ $incomeChart }}" style="width:100%;">
    @endif
</div>

<!-- TABLE -->
<div class="section">
    <h4>Data Group Customer</h4>

    <table>
        <thead>
            <tr>
                <th>Group</th>
                <th>Customer</th>
                <th>Invoice</th>
                <th>Lunas</th>
                <th>Belum</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($groupCustomers as $g)
            <tr>
                <td>{{ $g->group_name }}</td>
                <td>{{ $g->total_customer }}</td>
                <td>{{ $g->total_invoice }}</td>
                <td>{{ $g->invoice_lunas }}</td>
                <td>{{ $g->invoice_belum }}</td>
                <td>{{ number_format($g->total_semua) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="footer">
    Semarang, {{ now()->format('d M Y') }}
    <br>
    <br>
    <br>
    <br>
    <br>
    <div style="margin-top:50px;">
        (__________________)
        <br>Penanggung Jawab
    </div>
</div>

</body>
</html>