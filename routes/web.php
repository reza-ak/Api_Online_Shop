<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// callback url payment
Route::get('/payment/verify', function (Request $request) {
    $response = Http::post('http://localhost:8000/api/payment/verify', [
        'token' => $request->token,
        'status' => $request->status,
    ]);
    return $response->body();
});
