<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductCategoryController;
use App\Http\Controllers\ProductController;

// Route::apiResource(
//     '/product-categories',
//     ProductCategoryController::class
// )->only('index');
// Route::apiResource(
//     '/products',
//     ProductController::class
// )->only('index');
// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::put('/products/{product}/toggle-visibility', [ProductController::class, 'toggleVisibility']);
Route::post('/products/{product}/sync-to-hub', [ProductController::class, 'syncProductToHub']);
Route::delete('/products/{product}/delete-from-hub', [ProductController::class, 'deleteProductFromHub']);


