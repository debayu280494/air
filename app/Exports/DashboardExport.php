<?php

namespace App\Exports;

use App\Models\Bill;
use Maatwebsite\Excel\Concerns\FromCollection;

class DashboardExport implements FromCollection
{
    protected $year;
    protected $month;

    public function __construct($year, $month)
    {
        $this->year = $year;
        $this->month = $month;
    }

    public function collection()
    {
        $query = Bill::query()
            ->whereYear('created_at', $this->year);

        if ($this->month) {
            $query->whereMonth('created_at', $this->month);
        }

        return $query->get();
    }
}