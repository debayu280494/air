<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usage;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class UsageController extends Controller
{
    public function export(Request $request)
    {
        $query = Usage::with(['customer', 'bill']);

        // ================= FILTER PERIODE =================
        if (
            $request->start_month && $request->start_year &&
            $request->end_month && $request->end_year
        ) {
            $start = ($request->start_year * 100) + $request->start_month;
            $end = ($request->end_year * 100) + $request->end_month;

            $query->whereRaw('(year * 100 + month) BETWEEN ? AND ?', [$start, $end]);
        }

        // ================= FILTER STATUS =================
        if ($request->status) {
            $query->whereHas('bill', function ($q) use ($request) {
                $q->where('status', $request->status);
            });
        }

        // ================= FILTER GROUP =================
        if ($request->group) {
            $query->whereHas('customer', function ($q) use ($request) {
                $q->where('group_name', $request->group);
            });
        }

        // ================= AMBIL DATA + URUTKAN GRUP =================
        $usages = $query
            ->join('customers', 'customers.id', '=', 'usages.customer_id')
            ->orderBy('customers.group_name', 'asc') // 🔥 urut A-Z
            ->select('usages.*')
            ->with(['customer', 'bill'])
            ->get();

        // ================= GROUPING =================
        $grouped = $usages->groupBy(function ($item) {
            return $item->customer->group_name ?? 'Tanpa Grup';
        });

        // ================= TOTAL =================
        $grandTotal = $usages->sum('total_bill');

        return Pdf::loadView('pdf.usage-report', [
            'grouped' => $grouped,
            'grandTotal' => $grandTotal,
            'request' => $request,
        ])->stream('laporan-usage.pdf');
    }
}