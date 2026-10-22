<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderInvoiceController extends Controller
{
    /**
     * Display a clean, printable invoice for an order.
     */
    public function show(Request $request, Order $order): View
    {
        // Eager load items, product, variant to avoid N+1 queries
        $order->load(['items.product', 'items.variant', 'user', 'payments', 'statusHistories']);

        return view('admin.orders.invoice', [
            'order' => $order,
        ]);
    }
}
