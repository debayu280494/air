<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    protected $fillable = [
        'usage_id',
        'customer_id',
        'month',
        'year',
        'total_bill',
        'status',
        'payment_method', // 🔥 WAJIB
        'paid_at',
        
    ];

    public function usage()
    {
        return $this->belongsTo(Usage::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }
}