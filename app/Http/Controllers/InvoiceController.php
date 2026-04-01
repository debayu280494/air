<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use Barryvdh\DomPDF\Facade\Pdf;


class InvoiceController extends Controller
{
    public function show($id)
    {
        $bill = Bill::with(['customer', 'usage'])->findOrFail($id);

        return view('invoice.show', compact('bill'));
    }

    public function download($id)
    {
        $bill = Bill::with('customer','usage')->findOrFail($id);

        $pdf = Pdf::loadView('invoice.show', compact('bill'));

        return $pdf->download('invoice-'.$bill->invoice_number.'.pdf');
    }
}