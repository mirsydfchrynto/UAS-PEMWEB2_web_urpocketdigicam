<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Http\Resources\ProductResource;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::latest()->paginate(10);
        return new ProductResource(true, 'List Data Product', $products);
    }

    // public function index()
    // {
    //     $products = Product::all(); // Atau pakai pagination: Product::paginate(10)

    //     // Bisa return JSON untuk API
    //     return response()->json([
    //         'message' => 'List of products',
    //         'data' => $products
    //     ]);

    //     // Atau return view jika untuk halaman web:
    //     // return view('products.index', compact('products'));
    // }

}
