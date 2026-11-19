<?php

namespace App\Http\Controllers\Api\Public;

use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class CallbackController extends Controller
{
    /**
     * Handle Midtrans payment notification (webhook).
     */
    public function __invoke(Request $request)
    {
        $notification = json_decode($request->getContent());

        Log::info('Midtrans callback received', ['payload' => $request->getContent()]);

        if (!$notification || !isset($notification->order_id, $notification->status_code, $notification->gross_amount, $notification->signature_key)) {
            return response()->json(['message' => 'Invalid payload'], 400);
        }

        $serverKey         = config('midtrans.server_key');
        $validSignatureKey = hash('sha512', $notification->order_id . $notification->status_code . $notification->gross_amount . $serverKey);

        if (!hash_equals($validSignatureKey, $notification->signature_key)) {
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $transaction = Transaction::where('invoice', $notification->order_id)->first();

        if (!$transaction) {
            return response()->json(['message' => 'Transaction not found'], 404);
        }

        $status = Transaction::mapMidtransStatus(
            $notification->transaction_status ?? null,
            $notification->fraud_status ?? null
        );

        if ($status) {
            $transaction->update(['status' => $status]);
        }

        return response()->json(['message' => 'OK', 'status' => $transaction->status]);
    }
}
