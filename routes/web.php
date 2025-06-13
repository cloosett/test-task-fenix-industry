<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Auth routes
require __DIR__.'/auth.php';

// Home page
Route::get('/', [PageController::class, 'home'])->name('home');

// Cart page
Route::get('/cart', [PageController::class, 'cart'])->name('cart');

// Product routes
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');

// Admin product routes (can be protected with middleware later)
Route::post('/products', [ProductController::class, 'store'])->name('products.store');
Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

// Cart routes
Route::post('/cart/add', [CartController::class, 'addToCart'])->name('cart.add');
Route::put('/cart/items/{cartItem}', [CartController::class, 'updateQuantity'])->name('cart.update');
Route::delete('/cart/items/{cartItem}', [CartController::class, 'removeFromCart'])->name('cart.remove');
Route::delete('/cart/clear', [CartController::class, 'clearCart'])->name('cart.clear');
Route::post('/cart/checkout', [CartController::class, 'checkout'])->name('cart.checkout');

// AJAX routes for dynamic cart updates
Route::patch('/cart/items/{cartItem}/ajax', [CartController::class, 'updateQuantityAjax'])->name('cart.update.ajax');
Route::delete('/cart/items/{cartItem}/ajax', [CartController::class, 'removeFromCartAjax'])->name('cart.remove.ajax');
Route::get('/cart/summary', [CartController::class, 'getCartSummary'])->name('cart.summary');

// Merge guest cart when user logs in
Route::post('/cart/merge', [CartController::class, 'mergeGuestCart'])->name('cart.merge')->middleware('auth');
