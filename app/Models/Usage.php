<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Usage extends Model
{
    protected $fillable = [
        'customer_id',
        'month',
        'year',
        'meter_start',
        'meter_end',
        'usage',
        'total_bill',
        'status',
    ];

    protected $casts = [
        'meter_start' => 'integer',
        'meter_end' => 'integer',
        'usage' => 'integer',
        'total_bill' => 'integer',
    ];
    
    public function customer()
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    public function bill()
    {
        return $this->hasOne(Bill::class);
    }

    // 🔥 ambil data terakhir customer
    public static function lastByCustomer($customerId)
    {
        return self::where('customer_id', $customerId)
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->orderByDesc('id')
            ->first();
    }


    // 🔥 cek duplikat
    public static function isDuplicate($customerId, $month, $year, $ignoreId = null)
    {
        return self::where('customer_id', $customerId)
            ->where('month', $month)
            ->where('year', $year)
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->exists();
    }

    // 🔥 cek urutan bulan
    public static function isNextMonthValid($customerId, $year, $month)
    {
        $last = self::lastByCustomer($customerId);

        if (!$last) return true;

        $lastIndex = ($last->year * 12) + $last->month;
        $currentIndex = ($year * 12) + $month;

        return $currentIndex === ($lastIndex + 1);
    }

    public static function getLastIds()
    {
        return self::selectRaw('MAX(id) as id')
            ->groupBy('customer_id')
            ->pluck('id')
            ->toArray();
    }
    
}