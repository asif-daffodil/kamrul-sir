<?php

use App\Http\Controllers\Admin\OrderInvoiceController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Admin Invoice Route
Route::get('/admin/orders/{order}/invoice', [OrderInvoiceController::class, 'show'])
    ->name('admin.orders.invoice');
