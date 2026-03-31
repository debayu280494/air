<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'customer_code',
        'name',
        'address',
        'phone',
        'group_name',
        'status',
        'service_id',
    ];
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function usages()
    {
        return $this->hasMany(Usage::class);
    }
}
