<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'name',
        'price_per_meter',
        'maintenance_fee',
    ];

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }
}
