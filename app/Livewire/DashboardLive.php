<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Bill;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DashboardExport;

class DashboardLive extends Component
{
    public $year;
    public $month;
    public $groupCustomersCache;

    public function mount()
    {
        $this->year = now()->year;
        $this->month = now()->month;

        $this->dispatchChart(); // 🔥 render awal
    }

    public function updated($property)
    {
        $this->groupCustomersCache = null; // reset cache
        $this->dispatchChart();
    }

    public function export()
    {
        return response()->streamDownload(function () {
            echo Excel::raw(
                new DashboardExport($this->year, $this->month),
                \Maatwebsite\Excel\Excel::XLSX
            );
        }, 'dashboard.xlsx');
    }

    private function baseQuery()
    {
        return Bill::query()
            ->where('year', $this->year)
            ->when($this->month, fn($q) =>
                $q->where('month', $this->month)
            );
    }

    private function dispatchChart()
    {
        $groupCustomers = $this->getGroupCustomers();
        // ================= BAR (JANGAN FILTER BULAN) =================
        $monthly = Bill::query()
            ->where('year', $this->year)
            ->select(
                DB::raw('MONTH(created_at) as bulan'),
                DB::raw('SUM(total_bill) as total')
            )
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get();

        // 🔥 KIRIM KE JS (ingat: array di Livewire v3)
        $this->dispatch('updateCharts', [
            'pieLabels' => $groupCustomers->pluck('group_name')->values()->toArray(),
            'pieData'   => $groupCustomers->pluck('total_customer')->values()->toArray(),
            'barLabels' => $monthly->pluck('bulan')->values()->toArray(),
            'barData'   => $monthly->pluck('total')->values()->toArray(),
        ]);
    }

    private function getGroupCustomers()
    {
        if ($this->groupCustomersCache) {
            return $this->groupCustomersCache;
        }

        return $this->groupCustomersCache = Customer::leftJoin('bills', function ($join) {
                $join->on('customers.id','=','bills.customer_id')
                    ->where('bills.year', $this->year);

                if ($this->month) {
                    $join->where('bills.month', $this->month);;
                }
            })
            ->select(
                'customers.group_name',
                DB::raw('COUNT(DISTINCT customers.id) as total_customer'),
                DB::raw('COUNT(bills.id) as total_invoice'),
                DB::raw("SUM(CASE WHEN bills.status='lunas' THEN 1 ELSE 0 END) as invoice_lunas"),
                DB::raw("SUM(CASE WHEN bills.status='belum' THEN 1 ELSE 0 END) as invoice_belum"),
                DB::raw("SUM(CASE WHEN bills.status='lunas' THEN bills.total_bill ELSE 0 END) as total_lunas"),
                DB::raw("SUM(CASE WHEN bills.status='belum' THEN bills.total_bill ELSE 0 END) as total_belum"),
                DB::raw("COALESCE(SUM(bills.total_bill),0) as total_semua")
            )
            ->groupBy('customers.group_name')
            ->get();
    }

    public function render()
    {
        $query = $this->baseQuery();

        // ================= SUMMARY =================
        $total = (clone $query)->sum('total_bill');
        $belum = (clone $query)->where('status','belum')->sum('total_bill');
        $lunas = (clone $query)->where('status','lunas')->sum('total_bill');

        // ================= GROUP =================
        $groupCustomers = $this->getGroupCustomers();

        return view('livewire.dashboard-live', compact(
            'total',
            'belum',
            'lunas',
            'groupCustomers'
        ));
    }
}