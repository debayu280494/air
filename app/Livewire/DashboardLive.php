<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Bill;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Attributes\On;

class DashboardLive extends Component
{
    public $year;
    public $month;
    public $groupCustomersCache;

    public function mount()
    {
        $this->year = now()->year;
        $this->month = now()->month;

        $this->dispatchChart();
    }

    public function updated()
    {
        $this->groupCustomersCache = null;
        $this->dispatchChart();
    }

    // ================= EXPORT PDF =================
    #[On('exportPdfWithCharts')]
    public function exportPdfWithCharts($pie = null, $bar = null, $income = null)
    {
        $query = $this->baseQuery();

        $total = (clone $query)->sum('total_bill');
        $belum = (clone $query)->where('status', 'belum')->sum('total_bill');
        $lunas = (clone $query)->where('status', 'lunas')->sum('total_bill');

        $groupCustomers = $this->getGroupCustomers();

        $pdf = Pdf::loadView('pdf.dashboard-pdf', [
            'year' => $this->year,
            'month' => $this->month,
            'total' => $total,
            'belum' => $belum,
            'lunas' => $lunas,
            'groupCustomers' => $groupCustomers,

            // chart image
            'pieChart' => $pie,
            'barChart' => $bar,
            'incomeChart' => $income,
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn() => print($pdf->output()),
            'dashboard.pdf'
        );
    }

    // ================= QUERY TAGIHAN =================
    private function baseQuery()
    {
        return Bill::query()
            ->where('year', $this->year)
            ->when($this->month, fn($q) =>
                $q->where('month', $this->month)
            );
    }

    // ================= CHART =================
    private function dispatchChart()
    {
        $groupCustomers = $this->getGroupCustomers();

        // 📊 TAGIHAN (BULAN)
        $monthlyBill = Bill::query()
            ->where('year', $this->year)
            ->when($this->month, function ($q) {
                $q->where('month', $this->month);
            })
            ->select(
                DB::raw('month as bulan'),
                DB::raw('SUM(total_bill) as total')
            )
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get();

        // 💰 PEMASUKAN (PAID_AT)
        $incomeMonthly = Bill::query()
            ->where('status', 'lunas')
            ->whereNotNull('paid_at')
            ->whereYear('paid_at', $this->year)
            ->when($this->month, function ($q) {
                $q->whereMonth('paid_at', $this->month);
            })
            ->select(
                DB::raw('MONTH(paid_at) as bulan'),
                DB::raw('SUM(total_bill) as total')
            )
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get();

        $this->dispatch('updateCharts', [
            // PIE
            'pieLabels' => $groupCustomers->pluck('group_name')->values(),
            'pieData'   => $groupCustomers->pluck('total_customer')->values(),

            // BAR TAGIHAN
            'barLabels' => $monthlyBill->pluck('bulan')->values(),
            'barData'   => $monthlyBill->pluck('total')->values(),

            // 🔥 BAR PEMASUKAN
            'incomeLabels' => $incomeMonthly->pluck('bulan')->values(),
            'incomeData'   => $incomeMonthly->pluck('total')->values(),
        ]);
    }

    // ================= GROUP CUSTOMER =================
    private function getGroupCustomers()
    {
        return Customer::query()
            ->leftJoin('bills', 'customers.id', '=', 'bills.customer_id')
            ->where(function ($q) {
                $q->where('bills.year', $this->year);

                if ($this->month) {
                    $q->where('bills.month', $this->month);
                }
            })
            ->select(
                'customers.group_name',

                DB::raw('COUNT(DISTINCT customers.id) as total_customer'),
                DB::raw('COUNT(bills.id) as total_invoice'),

                DB::raw("SUM(CASE WHEN bills.status='lunas' THEN 1 ELSE 0 END) as invoice_lunas"),
                DB::raw("SUM(CASE WHEN bills.status='belum' THEN 1 ELSE 0 END) as invoice_belum"),

                DB::raw("COALESCE(SUM(bills.total_bill),0) as total_semua")
            )
            ->groupBy('customers.group_name')
            ->get();
    }

    // ================= RENDER =================
    public function render()
    {
        $query = $this->baseQuery();

        return view('livewire.dashboard-live', [
            'total' => $query->sum('total_bill'),
            'belum' => (clone $query)->where('status', 'belum')->sum('total_bill'),
            'lunas' => (clone $query)->where('status', 'lunas')->sum('total_bill'),
            'groupCustomers' => $this->getGroupCustomers()
        ]);
    }
}