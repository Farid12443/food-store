<?php

namespace App\Http\Controllers\Api\Public;

use Midtrans\Snap;
use App\Models\Cart;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\HasMiddleware;

class CheckoutController extends Controller implements HasMiddleware
{
    // property
    public $response;

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
     * __construct
     *
     * @return void
     */
    public function __construct()
    {
        // Set midtrans configuration
        \Midtrans\Config::$serverKey    = config('midtrans.server_key');
        \Midtrans\Config::$isProduction = config('midtrans.is_production');
        \Midtrans\Config::$isSanitized  = config('midtrans.is_sanitized');
        \Midtrans\Config::$is3ds        = config('midtrans.is_3ds');
    }

    public function store(Request $request)
    {
        DB::transaction(function() use ($request) {

            //create invoice code
            $invoice = 'INV-' . mt_rand(1000, 9999);

            //create transaction
            $transaction = Transaction::create([
                'customer_id'       => auth()->guard('api')->user()->id,
                'invoice'           => $invoice,
                'province_name'     => $request->province_name,
                'city_name'         => $request->city_name,
                'district_name'     => $request->district_name,
                'subdistrict_name'  => $request->subdistrict_name,
                'zip_code'          => $request->zip_code,
                'full_address'      => $request->full_address,
                'weight'            => $request->weight,
                'total_price'       => $request->total, // Adjusted from total to total_price
                'status'            => 'PENDING',
            ]);

            //create shipping
            $transaction->shipping()->create([
                'transaction_id'    => $transaction->id,
                'shipping_courier'  => $request->shipping_courier,
                'shipping_service'  => $request->shipping_service,
                'shipping_cost'     => $request->shipping_cost
            ]);

            //create items details
            $item_details = [];

            foreach (Cart::where('customer_id', auth()->guard('api')->user()->id)->get() as $cart) {

                //insert product ke table order
                $transaction->transactionDetails()->create([
                    'transaction_id'    => $transaction->id,
                    'product_id'        => $cart->product->id,
                    'qty'               => $cart->qty,
                    'price'             => $cart->product->price * $cart->qty,
                ]);

                $item_details[] = [
                    'id'        => $cart->product->id,
                    'price'     => (int)$cart->product->price, // Use individual price
                    'quantity'  => (int)$cart->qty,
                    'name'      => $cart->product->title
                ];

            }

            //item detail shipping
            $item_details[] = [
                'id'        => 'SHIPPING',
                'price'     => (int)$request->shipping_cost,
                'quantity'  => 1,
                'name'      => 'Shipping Cost'
            ];

            //remove cart by customer
            Cart::with('product')
                ->where('customer_id', auth()->guard('api')->user()->id)
                ->delete();

            // Buat transaksi ke midtrans kemudian save snap tokennya.
            $payload = [
                'transaction_details' => [
                    'order_id'     => $transaction->invoice,
                    'gross_amount' => (int)$transaction->total_price,
                ],
                'customer_details' => [
                    'first_name'        => auth()->guard('api')->user()->name,
                    'email'             => auth()->guard('api')->user()->email,
                    'shipping_address'  => $transaction->full_address // Adjusted from address
                ],
                'item_details' => $item_details
            ];

            //create snap token
            $snapToken = Snap::getSnapToken($payload);

            //update snap_token
            $transaction->snap_token = $snapToken;
            $transaction->save();

            //make response "snap_token"
            $this->response['snap_token'] = $snapToken;

        });

        //return response JSON
        return response()->json([
            'success' => true,
            'message' => 'Checkout Berhasil!',
            'data'    => $this->response
        ]);
    }
}
