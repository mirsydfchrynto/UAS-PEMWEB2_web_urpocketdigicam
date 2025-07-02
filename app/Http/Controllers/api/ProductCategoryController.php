<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Categories;
use App\Http\Resources\ProductCategoryResource;

class ProductCategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = Categories::latest()->paginate(10);
        return new ProductCategoryResource(true, 'List Data Product Category', $categories);
    }
}
