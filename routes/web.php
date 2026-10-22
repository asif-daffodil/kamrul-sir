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
Route::get('/checkout', \App\Livewire\Checkout\CheckoutPage::class)->name('checkout.index');
Route::get('/order-success/{orderNumber}', \App\Livewire\Checkout\OrderSuccessPage::class)->name('checkout.success');

// Order Tracking (Public)
Route::get('/track-order', \App\Livewire\Storefront\TrackOrderPage::class)->name('track-order');

// Customer Authentication (Guest)
Route::middleware('guest')->group(function () {
    Route::get('/login', \App\Livewire\Auth\LoginPage::class)->name('login');
    Route::get('/register', \App\Livewire\Auth\RegisterPage::class)->name('register');
});

// Logout
Route::post('/logout', function () {
    \Illuminate\Support\Facades\Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('home');
})->name('logout')->middleware('auth');

// Customer Account Portal (Auth)
Route::middleware('auth')->prefix('account')->group(function () {
    Route::get('/', \App\Livewire\Account\AccountDashboard::class)->name('account.dashboard');
    Route::get('/orders/{orderNumber}', \App\Livewire\Account\OrderDetailPage::class)->name('account.order.show');
});

Route::get('/api/search', [StorefrontController::class, 'search'])->name('storefront.search');
Route::get('/api/cart/count', [StorefrontController::class, 'cartCount'])->name('storefront.cart.count');
Route::post('/api/cart/quick-add', [StorefrontController::class, 'quickAddToCart'])->name('storefront.cart.quick-add');

// Admin Invoice Route
Route::get('/admin/orders/{order}/invoice', [OrderInvoiceController::class, 'show'])
    ->name('admin.orders.invoice');

// SSLCommerz Payment Gateway Callbacks & IPN
Route::post('/payment/sslcommerz/success', [\App\Http\Controllers\Payment\SslCommerzCallbackController::class, 'success'])
    ->name('payment.sslcommerz.success');
Route::post('/payment/sslcommerz/fail', [\App\Http\Controllers\Payment\SslCommerzCallbackController::class, 'fail'])
    ->name('payment.sslcommerz.fail');
Route::post('/payment/sslcommerz/cancel', [\App\Http\Controllers\Payment\SslCommerzCallbackController::class, 'cancel'])
    ->name('payment.sslcommerz.cancel');
Route::post('/payment/sslcommerz/ipn', [\App\Http\Controllers\Payment\SslCommerzCallbackController::class, 'ipn'])
    ->name('payment.sslcommerz.ipn');

// Courier Delivery Status Webhooks (Steadfast & Pathao)
Route::post('/api/webhooks/courier/steadfast', [\App\Http\Controllers\Courier\CourierWebhookController::class, 'steadfast'])
    ->name('webhook.courier.steadfast');
Route::post('/api/webhooks/courier/pathao', [\App\Http\Controllers\Courier\CourierWebhookController::class, 'pathao'])
    ->name('webhook.courier.pathao');
