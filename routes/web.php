<?php

use App\Http\Controllers\Admin\OrderInvoiceController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\StorefrontController;

Route::get('/', [StorefrontController::class, 'index'])->name('home');
Route::get('/api/search', [StorefrontController::class, 'search'])->name('storefront.search');
Route::get('/api/cart/count', [StorefrontController::class, 'cartCount'])->name('storefront.cart.count');
Route::post('/api/cart/quick-add', [StorefrontController::class, 'quickAddToCart'])->name('storefront.cart.quick-add');

// Admin Invoice Route
Route::get('/admin/orders/{order}/invoice', [OrderInvoiceController::class, 'show'])
    ->name('admin.orders.invoice');
