<?php

namespace App\Http\Controllers\Api\Customer;

use App\Models\Customer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Routing\Controllers\HasMiddleware;

class MyProfileController extends Controller implements HasMiddleware
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
        $profile = Customer::query()
            ->where('id', auth()->guard('api')->user()->id)
            ->firstOrFail();

        return response()->json([
            'success'   => true,
            'message'   => 'Detail Profil',
            'customer'  => $profile
        ]);
    }

    /**
     * update
     *
     * @param  mixed $request
     * @return void
     */
    public function update(Request $request)
    {
        //set validation
        $validator = Validator::make($request->all(), [
            'name'     => 'required',
            'email'    => 'required|email|unique:customers,email,'. auth()->guard('api')->user()->id,
            'password' => 'nullable|min:6|confirmed',
            'image'    => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Ambil profil customer yang login
        $profile = Customer::query()
            ->where('id', auth()->guard('api')->user()->id)
            ->firstOrFail();

        // Jika ada upload gambar, simpan dan update
        if ($request->hasFile('image')) {

            // Hapus gambar lama jika ada
            if ($profile->image) {
                Storage::delete('avatars/' . $profile->image);
            }

            // Upload gambar baru
            $image = $request->file('image');
            $image->storeAs('avatars', $image->hashName());

            // Simpan nama file ke database
            $profile->image = $image->hashName();
        }

        // Update data profil
        $profile->name = $request->name;
        $profile->email = $request->email;

        // Update password jika diberikan
        if ($request->filled('password')) {
            $profile->password = bcrypt($request->password);
        }

        // Simpan perubahan
        $profile->save();

        return response()->json([
            'success' => true,
            'message' => 'Update Profil Berhasil',
            'data'    => $profile
        ]);
    }
}
