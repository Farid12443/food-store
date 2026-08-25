<?php

namespace App\Http\Controllers\Api\Customer;

use App\Models\Transaction;
use App\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\HasMiddleware;

class MyOrderController extends Controller implements HasMiddleware
{
    /**
     * middleware
     *
     * @return array
     */
    public static function middleware(): array
    {
        return [
            'auth:api'
        ];
    }

    /**
     * index
     *
     * @return void
     */
    public function index()
    {
        //get transactions
        $transactions = Transaction::query()
            ->with('customer')
            ->where('customer_id', auth()->guard('api')->user()->id)
            ->latest()
            ->paginate(5);

        //return response JSON transactions
        return response()->json([
            'success'   => true,
            'message'   => 'List Pesanan: ' . auth()->guard('api')->user()->name,
            'data'      => $transactions
        ]);
    }

    /**
     * show
     *
     * @param  mixed $snap_token
     * @return void
     */
    public function show($snap_token)
    {
        //get transaction
        $transaction = Transaction::query()
            ->with( 'customer', 'shipping', 'transactionDetails.product')
            ->where('customer_id', auth()->guard('api')->user()->id)
            ->where('snap_token', $snap_token)
            ->firstOrFail();

        //return response JSON transaction
        return response()->json([
            'success'   => true,
            'message'   => 'Detail Pesanan',
            'data'      => $transaction
        ]);
    }
}
