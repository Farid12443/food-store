<?php

namespace App\Http\Controllers\Api\Public;

use App\Models\Cart;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\HasMiddleware;

class CartController extends Controller implements HasMiddleware
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
        //get carts by customer
        $carts = Cart::query()
            ->with('product')
            ->where('customer_id', auth()->guard('api')->user()->id)
            ->latest()
            ->get();

        // Menghitung total berat
        $totalWeight = $carts->sum(function ($cart) {
            return ($cart->product->weight ?? 0) * $cart->qty;
        });

        // Menghitung total harga
        $totalPrice = $carts->sum(function ($cart) {
            return ($cart->product->price ?? 0) * $cart->qty;
        });

        //return response JSON carts
        return response()->json([
            'success'   => true,
            'message'   => 'List Keranjang: ' . auth()->guard('api')->user()->name,
            'data'      => [
                'total_weight'  => $totalWeight,
                'total_price'   => $totalPrice,
                'carts'         => $carts
            ]
        ]);
    }

    /**
     * store
     *
     * @param  mixed $request
     * @return void
     */
    public function store(Request $request)
    {
        //check cart
        $item = Cart::where('product_id', $request->product_id)
                    ->where('customer_id', auth()->guard('api')->user()->id)
                    ->first();

        //if cart already exist
        if ($item) {

            //update cart qty
            $item->increment('qty');

        } else {

            //store cart
            $item = Cart::create([
                'customer_id'   => auth()->guard('api')->user()->id,
                'product_id'    => $request->product_id,
                'qty'           => $request->qty ?? 1
            ]);

        }

        //return response JSON cart is created
        return response()->json([
            'success'   => true,
            'message'   => 'Produk ditambahkan ke keranjang',
            'data'      => $item
        ]);
    }

    /**
     * IncrementCart
     *
     * @param  mixed $request
     * @return void
     */
    public function IncrementCart(Request $request)
    {
        //check cart
        $item = Cart::where('product_id', $request->product_id)
                    ->where('customer_id', auth()->guard('api')->user()->id)
                    ->where('id', $request->cart_id)
                    ->first();

        //if cart already exist
        if ($item) {

            //update cart qty
            $item->increment('qty');

        }

        //return response JSON cart is created
        return response()->json([
            'success'   => true,
            'message'   => 'Qty Keranjang Berhasil Ditambahkan',
            'data'      => $item
        ]);
    }

    /**
     * DecrementCart
     *
     * @param  mixed $request
     * @return void
     */
    public function DecrementCart(Request $request)
    {
        //check cart
        $item = Cart::where('product_id', $request->product_id)
                    ->where('customer_id', auth()->guard('api')->user()->id)
                    ->where('id', $request->cart_id)
                    ->first();

        //if cart already exist
        if ($item) {

            //update cart qty
            $item->decrement('qty');

        }

        //return response JSON cart is created
        return response()->json([
            'success'   => true,
            'message'   => 'Qty Keranjang Berhasil Dikurangi',
            'data'      => $item
        ]);
    }

    /**
     * destroy
     *
     * @param  mixed $id
     * @return void
     */
    public function destroy($id)
    {
        //get cart by id
        $cart = Cart::find($id);

        //delete cart
        if ($cart) {
            $cart->delete();
        }

        //return response JSON cart is deleted
        return response()->json([
            'success'   => true,
            'message'   => 'Keranjang Berhasil Dihapus',
            'data'      => $cart
        ]);
    }
}
