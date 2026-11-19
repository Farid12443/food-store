<?php

namespace App\Http\Controllers\Api\Customer;

use App\Models\Transaction;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
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

        // Fallback when the Midtrans webhook can't reach this server (e.g. localhost):
        // ask Midtrans directly for the latest status of a pending transaction.
        if ($transaction->status === 'pending') {
            $this->syncStatusFromMidtrans($transaction);
        }

        //return response JSON transaction
        return response()->json([
            'success'   => true,
            'message'   => 'Detail Pesanan',
            'data'      => $transaction
        ]);
    }

    /**
     * Pull the transaction status from Midtrans and update it locally.
     */
    private function syncStatusFromMidtrans(Transaction $transaction): void
    {
        try {
            \Midtrans\Config::$serverKey    = config('midtrans.server_key');
            \Midtrans\Config::$isProduction = config('midtrans.is_production');

            $result = \Midtrans\Transaction::status($transaction->invoice);

            $status = Transaction::mapMidtransStatus(
                $result->transaction_status ?? null,
                $result->fraud_status ?? null
            );

            if ($status && $status !== $transaction->status) {
                $transaction->update(['status' => $status]);
            }
        } catch (\Throwable $e) {
            // 404 = customer hasn't chosen a payment method yet; keep pending.
            Log::info('Midtrans status check skipped: ' . $e->getMessage(), ['invoice' => $transaction->invoice]);
        }
    }
}
