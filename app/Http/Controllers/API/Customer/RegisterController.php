<?php

namespace App\Http\Controllers\API\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Customer;

class RegisterController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:customers',
            'password' => 'required|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors(),
            ], 422);
        }

        $customer = Customer::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        if ($customer) {
            return response()->json([
                'success' => true,
                'message' => 'Register Berhasil',
                'data' => $customer,
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Register Gagal',
            ], 409);
        }
    }
}
