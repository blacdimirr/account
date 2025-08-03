<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiExternalController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/test', function () {
    return response()->json(['message' => 'API is working']);
});
Route::get('/products', [ApiExternalController::class, 'get_products']);
Route::get('/product/{sku}', [ApiExternalController::class, 'get_product_bySku']);
Route::get('/categories', [ApiExternalController::class, 'get_all_category']);
Route::get('/units', [ApiExternalController::class, 'get_all_units']);
Route::post('/update_product_bySku/{sku}/update', [ApiExternalController::class, 'update_product_bySku']);