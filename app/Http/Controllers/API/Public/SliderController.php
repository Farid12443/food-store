<?php

namespace App\Http\Controllers\Api\Public;

use App\Models\Slider;
use App\Http\Controllers\Controller;

class SliderController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke()
    {
        //get sliders
        $sliders = Slider::latest()->get();

        //return response JSON sliders
        return response()->json([
            'success'   => true,
            'message'   => 'List Slider',
            'data'      => $sliders
        ]);
    }
}
