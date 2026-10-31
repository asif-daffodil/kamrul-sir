<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $order->order_number }} — {{ config('shop.store.name', 'Heel Bari') }}</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }
        body {
            background-color: #f8fafc;
            color: #1e293b;
            padding: 30px 15px;
            font-size: 13px;
            line-height: 1.5;
        }
        .invoice-box {
            max-width: 800px;
            margin: auto;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 35px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 20px;
            margin-bottom: 24px;
        }
        .brand-title {
            font-size: 24px;
            font-weight: 800;
            color: #e11d48;
            letter-spacing: -0.5px;
        }
        .brand-sub {
            font-size: 11px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 2px;
        }
        .invoice-title {
            text-align: right;
        }
        .invoice-title h2 {
            font-size: 20px;
            font-weight: 700;
            color: #0f172a;
        }
        .order-meta {
            font-size: 12px;
            color: #475569;
            margin-top: 4px;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .badge-success { background: #dcfce7; color: #15803d; }
        .badge-warning { background: #fef3c7; color: #b45309; }
        .badge-info { background: #e0f2fe; color: #0369a1; }
        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 25px;
            background: #f8fafc;
            padding: 16px;
            border-radius: 8px;
        }
        .details-col h4 {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            margin-bottom: 6px;
        }
        .details-col p {
            font-size: 13px;
            color: #1e293b;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }
        th {
            background: #f1f5f9;
            color: #475569;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            text-align: left;
            padding: 10px 12px;
            font-weight: 600;
        }
        td {
            padding: 12px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .item-name {
            font-weight: 600;
            color: #0f172a;
        }
        .item-variant {
            font-size: 11px;
            color: #64748b;
            margin-top: 2px;
        }
        .totals-section {
            display: flex;
            justify-content: flex-end;
            margin-top: 10px;
        }
        .totals-table {
            width: 320px;
        }
        .totals-table td {
            padding: 6px 10px;
            border: none;
        }
        .grand-total {
            border-top: 2px solid #cbd5e1 !important;
            font-size: 16px;
            font-weight: 700;
            color: #e11d48;
        }
        .footer {
            margin-top: 35px;
            padding-top: 15px;
            border-top: 1px dashed #cbd5e1;
            font-size: 11px;
            color: #64748b;
            text-align: center;
        }
        .actions-bar {
            max-width: 800px;
            margin: 0 auto 20px auto;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        .btn {
            background: #0f172a;
            color: #ffffff;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
        }
        .btn:hover {
            background: #1e293b;
        }
        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }
            .invoice-box {
                border: none;
                box-shadow: none;
                padding: 10px;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="actions-bar no-print">
        <a href="javascript:window.print()" class="btn">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            Print Invoice
        </a>
    </div>

    <div class="invoice-box">
        <!-- Header -->
        <div class="header">
            <div>
                <img src="{{ asset('images/logo/heel-bari-logo.png') }}" alt="Heel Bari" style="height: 40px; width: auto; margin-bottom: 6px; display: block;">
                <div class="brand-sub">Handcrafted Women's Footwear • Bangladesh</div>
                <div style="margin-top: 6px; font-size: 11px; color: #475569;">
                    Support: {{ config('shop.store.phone', '+8801700000000') }}<br>
                    Email: {{ config('shop.store.email', 'care@heelbari.com') }}<br>
                    Dhaka, Bangladesh
                </div>
            </div>
            <div class="invoice-title">
                <h2>INVOICE</h2>
                <div class="order-meta">
                    <strong>Invoice #:</strong> {{ $order->order_number }}<br>
                    <strong>Date:</strong> {{ $order->created_at->format('d M, Y h:i A') }}<br>
                    <strong>Payment:</strong> <span class="badge badge-{{ $order->payment_status === 'paid' ? 'success' : 'warning' }}">{{ strtoupper($order->payment_status) }} ({{ strtoupper($order->payment_method) }})</span><br>
                    <strong>Status:</strong> <span class="badge badge-info">{{ strtoupper($order->status) }}</span>
                </div>
            </div>
        </div>

        <!-- Customer & Delivery Grid -->
        <div class="details-grid">
            <div class="details-col">
                <h4>Customer Details</h4>
                <p><strong>{{ $order->customer_name }}</strong></p>
                <p>Phone: {{ $order->customer_phone }}</p>
                @if($order->customer_email)
                    <p>Email: {{ $order->customer_email }}</p>
                @endif
            </div>
            <div class="details-col">
                <h4>Shipping Address</h4>
                <p>{{ $order->shipping_address }}</p>
                <p>{{ $order->shipping_thana }}, {{ $order->shipping_district }}</p>
                <p>Division: {{ $order->shipping_division }}</p>
            </div>
        </div>

        <!-- Itemized Table -->
        <table>
            <thead>
                <tr>
                    <th style="width: 50%;">Product Details</th>
                    <th class="text-right">Unit Price</th>
                    <th class="text-center">Qty</th>
                    <th class="text-right">Total (BDT)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                    <tr>
                        <td>
                            <div class="item-name">{{ $item->product_name }}</div>
                            @if($item->variant_details)
                                <div class="item-variant">
                                    Color: {{ $item->variant_details['color'] ?? 'Standard' }} | Size: EU {{ $item->variant_details['size'] ?? 'N/A' }}
                                    @if(!empty($item->variant_details['sku']))
                                        | SKU: {{ $item->variant_details['sku'] }}
                                    @endif
                                </div>
                            @endif
                        </td>
                        <td class="text-right">৳ {{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td class="text-right">৳ {{ number_format($item->total_price, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals Calculation -->
        <div class="totals-section">
            <table class="totals-table">
                <tr>
                    <td>Subtotal:</td>
                    <td class="text-right">৳ {{ number_format($order->subtotal, 2) }}</td>
                </tr>
                <tr>
                    <td>Delivery Charge ({{ $order->shipping_district }}):</td>
                    <td class="text-right">৳ {{ number_format($order->delivery_charge, 2) }}</td>
                </tr>
                @if($order->discount_amount > 0)
                    <tr style="color: #16a34a;">
                        <td>Discount ({{ $order->coupon_code ?? 'Promo' }}):</td>
                        <td class="text-right">- ৳ {{ number_format($order->discount_amount, 2) }}</td>
                    </tr>
                @endif
                <tr class="grand-total">
                    <td>Grand Total:</td>
                    <td class="text-right">৳ {{ number_format($order->total, 2) }}</td>
                </tr>
            </table>
        </div>

        @if($order->notes)
            <div style="margin-top: 15px; padding: 10px; background: #fffbeb; border-radius: 6px; font-size: 11px; color: #92400e;">
                <strong>Customer Note:</strong> {{ $order->notes }}
            </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p>Thank you for shopping with {{ config('shop.store.name', 'Heel Bari') }}!</p>
            <p>For exchanges or support, contact us within 7 days of delivery with your order invoice.</p>
        </div>
    </div>
</body>
</html>
