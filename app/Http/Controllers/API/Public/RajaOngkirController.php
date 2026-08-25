<?php

namespace App\Http\Controllers\Api\Public;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class RajaOngkirController extends Controller
{
    /**
     * searchDestination
     *
     * @param  mixed $request
     * @return void
     */
    public function searchDestination(Request $request)
    {
        $response = Http::withHeaders([
            'key' => config('rajaongkir.api_key')
        ])->get('https://rajaongkir.komerce.id/api/v1/destination/domestic-destination', [
            'search' => $request->search,
            'limit'  => 100,
            'offset' => 0
        ]);

        return response()->json($response['data']);
    }

    /**
     * checkOngkir
     *
     * @param  mixed $request
     * @return void
     */
    public function checkOngkir(Request $request)
    {
        //Fetch Rest API
        $response = Http::withHeaders([
            //api key rajaongkir
            'key'           => config('rajaongkir.api_key')
        ])->asForm()->post('https://rajaongkir.komerce.id/api/v1/calculate/domestic-cost', [

            //send data
            'origin'      => 46174, // ID desa "Bandung" Diwek Jombang
            'destination' => $request->destination,
            'weight'      => $request->weight,
            'courier'     => $request->courier
        ]);

        return response()->json($response['data']);
    }
}
