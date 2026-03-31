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
    
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
