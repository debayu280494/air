<!DOCTYPE html>
<html>
<head>
    <title>Invoice</title>
    <div>No: {{ $bill->invoice_number }}</div>

    <script>
        function printInvoice() {
            window.print();
        }
    </script>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f3f4f6;
            padding: 30px;
        }

        .invoice-box {
            max-width: 800px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .title {
            font-size: 24px;
            font-weight: bold;
        }

        .status {
            padding: 5px 10px;
            border-radius: 6px;
            font-size: 12px;
            color: white;
        }

        .paid { background: #16a34a; }
        .unpaid { background: #dc2626; }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table th {
            background: #f9fafb;
        }

        table th, table td {
            border: 1px solid #e5e7eb;
            padding: 10px;
            text-align: center;
        }

        .total {
            text-align: right;
            font-size: 18px;
            font-weight: bold;
            margin-top: 15px;
        }

        .print-btn {
            margin-top: 20px;
            background: #2563eb;
            color: white;
            padding: 10px 15px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
        }

        @media print {
            body { background: white; }
            .print-btn { display: none; }
        }
    </style>
</head>

<body>

<div class="invoice-box">

    <div class="header">
        <div>
            <img src="{{ asset('logo.png') }}" width="80">
            <div class="title">INVOICE</div>
        </div>
        <div>
            <div class="title">INVOICE</div>
            <div>No: INV-{{ $bill->id }}</div>
        </div>

        <div>
            <span class="status {{ $bill->status == 'lunas' ? 'paid' : 'unpaid' }}">
                {{ strtoupper($bill->status) }}
            </span>
        </div>
    </div>

    <hr>

    <p><b>Customer:</b> {{ $bill->customer->name }}</p>
    <p><b>Grup:</b> {{ $bill->customer->group_name ?? '-' }}</p>
    <p><b>Periode:</b> 
        {{ \Carbon\Carbon::create()->month($bill->month)->translatedFormat('F') }} {{ $bill->year }}
    </p>

    <table>
        <thead>
            <tr>
                <th>Meter Awal</th>
                <th>Meter Akhir</th>
                <th>Pemakaian</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $bill->usage->meter_start }}</td>
                <td>{{ $bill->usage->meter_end }}</td>
                <td>{{ $bill->usage->usage }}</td>
                <td>Rp {{ number_format($bill->total_bill, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="total">
        Total: Rp {{ number_format($bill->total_bill, 0, ',', '.') }}
    </div>

    @if($bill->status == 'lunas')
        <hr>
        <p><b>Metode:</b> {{ strtoupper($bill->payment_method) }}</p>
        <p><b>Tanggal Bayar:</b> {{ $bill->paid_at }}</p>
    @endif

    <button class="print-btn" onclick="printInvoice()">🖨️ Print Invoice</button>

</div>

</body>
</html>