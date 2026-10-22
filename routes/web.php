<?php

use App\Http\Controllers\Admin\OrderInvoiceController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductDetailsController;
use App\Http\Controllers\StorefrontController;

Route::get('/', [StorefrontController::class, 'index'])->name('home');
Route::get('/products', [CategoryController::class, 'index'])->name('products.index');
Route::get('/category/{slug}', [CategoryController::class, 'show'])->name('category.show');
Route::get('/product/{slug}', [ProductDetailsController::class, 'show'])->name('product.show');
Route::get('/cart', \App\Livewire\Cart\CartPage::class)->name('cart.index');

Route::get('/api/search', [StorefrontController::class, 'search'])->name('storefront.search');
Route::get('/api/cart/count', [StorefrontController::class, 'cartCount'])->name('storefront.cart.count');
Route::post('/api/cart/quick-add', [StorefrontController::class, 'quickAddToCart'])->name('storefront.cart.quick-add');

// Admin Invoice Route
Route::get('/admin/orders/{order}/invoice', [OrderInvoiceController::class, 'show'])
    ->name('admin.orders.invoice');
