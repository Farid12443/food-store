<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shipping extends Model
{
    protected $fillable = [
        'transaction_id',
        'shipping_courier',
        'shipping_service',
        'shipping_cost',
    ];
}
