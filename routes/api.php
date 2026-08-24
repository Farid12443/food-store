<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'customer'], function () {

    Route::post('/register', Customer\RegisterController::class)->name('customer.register');
});
