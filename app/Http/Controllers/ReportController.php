<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function monthly(Request $request)
    {
        $year = $request->year ?? now()->year;

        $data = Bill::select(
                DB::raw('MONTH(created_at) as bulan'),
                DB::raw('SUM(total_bill) as total')
            )
            ->whereYear('created_at', $year)
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get();

        return view('report.monthly', compact('data','year'));
    }

    public function monthlyPdf(Request $request)
    {
        $year = $request->year ?? now()->year;

        $data = Bill::select(
                DB::raw('MONTH(created_at) as bulan'),
                DB::raw('SUM(total_bill) as total')
            )
            ->whereYear('created_at', $year)
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get();

        $pdf = Pdf::loadView('report.monthly-pdf', compact('data','year'));

        return $pdf->download('laporan-'.$year.'.pdf');
    }
}