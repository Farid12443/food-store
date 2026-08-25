<?php

namespace App\Http\Controllers\Api\Customer;

use App\Models\Rating;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\HasMiddleware;

class RatingController extends Controller implements HasMiddleware
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
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        //check rating already
        $check_rating = Rating::query()
            ->where('product_id', $request->product_id)
            ->where('customer_id', auth()->guard('api')->user()->id)
            ->first();

        //if rating already
        if($check_rating) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah pernah memberikan rating untuk produk ini',
                'data'    => $check_rating
            ], 409);
        }

        //create rating
        $rating = Rating::create([
            'rating'                  => $request->rating,
            'review'                  => $request->review,
            'product_id'              => $request->product_id,
            'transaction_id'          => $request->transaction_id, // Adjusted from transaction_detail_id to match model
            'customer_id'             => auth()->guard('api')->user()->id
        ]);

        //return with response JSON
        return response()->json([
            'success' => true,
            'message' => 'Rating Berhasil Ditambahkan',
            'data' => $rating
        ]);
    }
}
