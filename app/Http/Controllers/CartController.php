<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $cart = Cart::firstOrCreate(['user_id' => $request->user()->id]);
        $cart->load('items.product');

        return view('cart.index', compact('cart'));
    }

    public function add(Request $request, string $slug)
    {
        $request->validate(['qty' => 'nullable|integer|min:1']);
        $product = Product::where('slug', $slug)->firstOrFail();

        $cart = Cart::firstOrCreate(['user_id' => $request->user()->id]);
        $cart->addProduct($product, (int) ($request->qty ?? 1));

        return back()->with('status', 'Produk ditambahkan ke keranjang.');
    }

    public function update(Request $request, int $itemId)
    {
        $request->validate(['qty' => 'required|integer|min:1|max:999']);
        $cart = Cart::firstOrCreate(['user_id' => $request->user()->id]);
        $item = $cart->items()->where('id', $itemId)->firstOrFail();
        $item->qty = (int) $request->qty;
        $item->save();

        return back()->with('status', 'Kuantitas diperbarui.');
    }

    public function remove(Request $request, int $itemId)
    {
        $cart = Cart::firstOrCreate(['user_id' => $request->user()->id]);
        $cart->items()->where('id', $itemId)->delete();

        return back()->with('status', 'Item dihapus.');
    }
}
