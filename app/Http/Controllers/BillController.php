<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bill;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class BillController extends Controller
{
    public function export(Request $request)
    {
        $query = Bill::with('customer')
            ->where('status', 'lunas'); // karena kamu mau hanya yang sudah bayar

        // 🔥 FILTER TANGGAL BAYAR
        if ($request->start_date && $request->end_date) {
            $start = Carbon::parse($request->start_date)->startOfDay();
            $end = Carbon::parse($request->end_date)->endOfDay();

            $query->whereBetween('paid_at', [$start, $end]);
        }

        // FILTER GROUP
        if ($request->group) {
            $query->whereHas('customer', function ($q) use ($request) {
                $q->where('group_name', $request->group);
            });
        }

        $bills = $query->get();

        // 🔥 GROUPING
        $grouped = $bills->groupBy(fn($b) => $b->customer->group_name ?? '-');

        // 🔥 TOTAL PER METODE
        $methodTotals = $bills->groupBy('payment_method')
            ->map(fn($items) => $items->sum('total_bill'));

        $grandTotal = $bills->sum('total_bill');

        return Pdf::loadView('pdf.bill-report', [
            'grouped' => $grouped,
            'methodTotals' => $methodTotals,
            'grandTotal' => $grandTotal,
            'request' => $request,
        ])->stream('laporan-tagihan.pdf');
    }
}