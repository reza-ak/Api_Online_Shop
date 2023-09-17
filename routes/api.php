<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Authentication
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::group(['middleware' => ['auth:sanctum']], function(){
    Route::post('/logout', [AuthController::class, 'logout']);
});

// brands
Route::apiResource('brands', BrandController::class);
Route::middleware('auth:sanctum')->get('/brands/{brands}/products', [BrandController::class, 'products']);

// categories
Route::apiResource('categories', CategoryController::class);
Route::get('/categories/{category}/children', [CategoryController::class, 'children']);
Route::get('/categories/{category}/parent', [CategoryController::class, 'parent']);
Route::get('/categories/{category}/products', [CategoryController::class, 'products']);

// products
Route::apiResource('products', ProductController::class);

// payment
Route::post('/payment/send', [PaymentController::class, 'send']);
Route::post('/payment/verify', [PaymentController::class, 'verify']);
