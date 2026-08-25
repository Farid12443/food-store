<?php

namespace App\Http\Controllers\Api\Customer;

use App\Models\Customer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        //set validation
        $validator = Validator::make($request->all(), [
            'name'     => 'required',
            'email'    => 'required|email|unique:customers',
            'password' => 'required|min:8|confirmed'
        ]);

        //if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //create customer
        $customer = Customer::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => bcrypt($request->password)
        ]);

        //return response JSON user is created
        if($customer) {
            return response()->json([
                'success'   => true,
                'message'   => 'Register Berhasil',
                'data'      => $customer,
            ], 201);
        }

        //return JSON process insert failed
        return response()->json([
            'success' => false,
            'message' => 'Register Gagal',
        ], 409);
    }
}
