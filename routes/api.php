<?php

use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::apiResource('brands', BrandController::class);
Route::get('/brands/{brands}/products', [BrandController::class, 'products']);

Route::apiResource('categories', CategoryController::class);
Route::get('/categories/{category}/children', [CategoryController::class, 'children']);
Route::get('/categories/{category}/parent', [CategoryController::class, 'parent']);

Route::apiResource('products', ProductController::class);
