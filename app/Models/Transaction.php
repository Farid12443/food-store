<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'customer_id',
        'province_name',
        'city_name',
        'district_name',
        'subdistrict_name',
        'zip_code',
        'full_address',
        'invoice',
        'weight',
        'total_price',
        'status',
        'snap_token',
    ];

    public function transactionDetails()
    {
        return $this->hasMany(TransactionDetail::class);
    }

    public function shipping()
    {
        return $this->hasOne(Shipping::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
