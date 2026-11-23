<?php

use App\Http\Controllers\CourierController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::apiResource('couriers', CourierController::class);
