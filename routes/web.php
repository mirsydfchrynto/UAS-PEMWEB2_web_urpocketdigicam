
<?php

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
// Route::put('/products/{product}/toggle-visibility', [ProductController::class, 'toggleVisibility']);
// Route::post('/products/{product}/sync-to-hub', [ProductController::class, 'syncProductToHub']);
// Route::delete('/products/{product}/delete-from-hub', [ProductController::class, 'deleteProductFromHub']);

// Route untuk web (jika menggunakan web authentication)
// Route::middleware('auth')->group(function () {
//     Route::get('/products', [ProductController::class, 'index'])->name('products.index');

// });
