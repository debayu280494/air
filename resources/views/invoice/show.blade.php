<!DOCTYPE html>
<html>
<head>
    <title>Invoice</title>

    <script>
        let isDownloadingInvoiceJpg = false;

        function printInvoice() {
            window.print();
        }

        function loadHtml2Canvas() {
            return new Promise((resolve, reject) => {
                if (window.html2canvas) {
                    resolve(window.html2canvas);
                    return;
                }

                const script = document.createElement('script');
                script.src = 'https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js';
                script.onload = () => resolve(window.html2canvas);
                script.onerror = () => reject(new Error('Gagal memuat html2canvas'));
                document.head.appendChild(script);
            });
        }

        async function downloadInvoiceJpg() {
            if (isDownloadingInvoiceJpg) {
                return;
            }

            isDownloadingInvoiceJpg = true;

            try {
                const html2canvas = await loadHtml2Canvas();
                const invoice = document.querySelector('.invoice-box');

                const canvas = await html2canvas(invoice, {
                    backgroundColor: '#ffffff',
                    scale: 2,
                    useCORS: true,
                    scrollX: 0,
                    scrollY: 0
                });

                const dataUrl = canvas.toDataURL('image/jpeg', 0.95);
                const link = document.createElement('a');
                link.href = dataUrl;
                link.download = 'invoice-INV-{{ $bill->id }}.jpg';
                document.body.appendChild(link);
                link.click();
                link.remove();
            } catch (error) {
                console.error(error);
                window.location.href = "{{ route('invoice.jpg', $bill->id) }}";
            } finally {
                isDownloadingInvoiceJpg = false;
            }
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
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
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

        .paid {
            background: #16a34a;
        }

        .unpaid {
            background: #dc2626;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table th {
            background: #f9fafb;
        }

        table th,
        table td {
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

        .download-btn {
            margin-top: 20px;
            margin-left: 10px;
            background: #059669;
            color: white;
            padding: 10px 15px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        @media print {
            body {
                background: white;
            }

            .print-btn,
            .download-btn {
                display: none;
            }
        }
    </style>
</head>

<body>

<div class="invoice-box">

    <div class="header">
        <div>
            <img src="{{ asset('logo.png') }}" width="80" alt="Logo">
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
    <p>
        <b>Periode:</b>
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

    <button class="print-btn" onclick="printInvoice()">Print Invoice</button>
    <button class="download-btn" type="button" onclick="downloadInvoiceJpg()">Cetak Invoice</button>

</div>

</body>
</html>
