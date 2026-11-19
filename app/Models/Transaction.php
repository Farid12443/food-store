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

    /**
     * Map a Midtrans transaction_status (+ fraud_status) to our local status.
     * Returns null when the status should not change anything.
     */
    public static function mapMidtransStatus(?string $transactionStatus, ?string $fraudStatus = null): ?string
    {
        return match ($transactionStatus) {
            'capture'    => ($fraudStatus === null || $fraudStatus === 'accept') ? 'success' : 'pending',
            'settlement' => 'success',
            'pending'    => 'pending',
            'expire'     => 'expired',
            'deny', 'cancel', 'failure' => 'failed',
            default      => null,
        };
    }

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
