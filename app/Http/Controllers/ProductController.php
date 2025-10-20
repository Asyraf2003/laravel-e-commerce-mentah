<?php

namespace App\Http\Controllers;

use App\Models\Product;

class ProductController extends Controller
{
    // Halaman utama = list produk (paginated)
    public function index()
    {
        $products = Product::query()->latest()->paginate(9);
        return view('products.index', compact('products'));
    }

    // Detail produk
    public function show(string $slug)
    {
        $product = Product::query()->where('slug', $slug)->firstOrFail();
        return view('products.show', compact('product'));
    }
}
