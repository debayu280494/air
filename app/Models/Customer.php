<?php

namespace App\Models;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use SoftDeletes;

    const STATUS_AKTIF = 'aktif';
    const STATUS_NONAKTIF = 'nonaktif';

    protected $fillable = [
        'name',
        'address',
        'phone',
        'group_name',
        'status',
        'service_id',
    ];

    protected $casts = [
        'status' => 'string',
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
