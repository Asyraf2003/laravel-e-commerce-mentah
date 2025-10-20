<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PaymentWebhookController;
use App\Http\Controllers\OrderPaymentController;

Route::get('/', [ProductController::class, 'index'])->name('products.index');
Route::get('/p/{slug}', [ProductController::class, 'show'])->name('products.show');
Route::post('/midtrans/notify', [PaymentWebhookController::class, 'handle'])->name('midtrans.notify');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('products.index');
    })->middleware(['verified'])->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Keranjang Belanja (Cart)
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add/{slug}', [CartController::class, 'add'])->name('cart.add');
    Route::patch('/cart/item/{itemId}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/item/{itemId}', [CartController::class, 'remove'])->name('cart.remove');
    
    // Checkout
    Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

    // AJAX Endpoints (untuk dropdown & ongkir di halaman checkout)
    Route::get('/checkout/search-destination', [CheckoutController::class, 'searchDestination'])->name('checkout.search-destination');
    Route::post('/checkout/costs', [CheckoutController::class, 'costs'])->name('checkout.costs');
    
    // Order Payment
    Route::get('/orders/{order}/refresh', [OrderPaymentController::class, 'refresh'])->name('orders.refresh');
    Route::get('/orders/{order}/finish', [OrderPaymentController::class, 'finish'])->name('orders.finish');
});

require __DIR__.'/auth.php';

