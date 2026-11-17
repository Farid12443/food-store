<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class RajaOngkirController extends Controller
{
    /**
     * Search destination
     */
    public function searchDestination(Request $request)
    {
        $validated = $request->validate([
            'search' => 'required|string|min:3',
        ]);

        $response = Http::withHeaders([
            'key' => config('rajaongkir.api_key'),
        ])->get(
            config('rajaongkir.base_url') . '/destination/domestic-destination',
            [
                'search' => $validated['search'],
                'limit'  => 100,
                'offset' => 0,
            ]
        );

        if ($response->failed()) {
            return response()->json([
                'message' => 'Gagal mencari destinasi',
                'error' => $response->json(),
            ], $response->status());
        }

        return response()->json($response->json('data'));
    }

    /**
     * Check shipping cost
     */
    public function checkOngkir(Request $request)
    {
        $validated = $request->validate([
            'destination' => 'required|integer',
            'weight'      => 'required|integer|min:1',
            'courier'     => 'required|string',
        ]);

        $response = Http::withHeaders([
            'key' => config('rajaongkir.api_key'),
        ])->asForm()->post(
            config('rajaongkir.base_url') . '/calculate/domestic-cost',
            [
                'origin'      => 46174,
                'destination' => $validated['destination'],
                'weight'      => $validated['weight'],
                'courier'     => $validated['courier'],
            ]
        );

        if ($response->failed()) {
            return response()->json([
                'message' => 'Gagal menghitung ongkir',
                'error' => $response->json(),
            ], $response->status());
        }

        return response()->json($response->json('data'));
    }
}
