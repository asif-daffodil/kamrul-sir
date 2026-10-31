<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-16">

    <!-- CELEBRATION HEADER CARD -->
    <div class="bg-white rounded-3xl border border-stone-200 p-8 sm:p-12 text-center shadow-xl space-y-5 relative overflow-hidden">
        <div class="absolute -top-12 -right-12 w-48 h-48 bg-rose-100/50 rounded-full blur-2xl pointer-events-none"></div>
        <div class="absolute -bottom-12 -left-12 w-48 h-48 bg-emerald-100/40 rounded-full blur-2xl pointer-events-none"></div>

        <!-- Success Icon -->
        <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center text-4xl sm:text-5xl mx-auto shadow-inner border-2 border-emerald-100">
            ✓
        </div>

        <div class="space-y-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-100/80 text-emerald-800 text-xs font-bold uppercase tracking-wider">
                <span class="w-2 h-2 rounded-full bg-emerald-600 animate-pulse"></span>
                Order Placed Successfully
            </span>
            <h1 class="text-2xl sm:text-4xl font-black text-slate-900 font-serif">
                Thank You, {{ $order->customer_name }}!
            </h1>
            <p class="text-xs sm:text-sm text-stone-600 max-w-lg mx-auto">
                We have received your footwear order. Our customer care team in Dhaka will call or WhatsApp you at <strong class="text-slate-900 font-mono">{{ $order->customer_phone }}</strong> to confirm dispatch.
            </p>
        </div>

        <!-- Order Number Badge -->
        <div class="inline-flex items-center gap-3 px-5 py-2.5 rounded-2xl bg-stone-100 border border-stone-200 text-slate-800 font-mono text-sm sm:text-base font-bold shadow-2xs">
            <span class="text-stone-400 font-normal">Order ID:</span>
            <span class="text-rose-950 font-black">{{ $order->order_number }}</span>
        </div>

        <!-- Timeline Banner -->
        <div class="p-4 rounded-2xl bg-stone-50 border border-stone-200 text-xs text-slate-700 flex flex-col sm:flex-row items-center justify-between gap-3 text-left">
            <div class="flex items-center gap-2.5">
                <span class="text-2xl">🚚</span>
                <div>
                    <p class="font-bold text-slate-900">
                        {{ $isDhaka ? 'Estimated Delivery: 24 to 48 Hours' : 'Estimated Delivery: 3 to 5 Business Days' }}
                    </p>
                    <p class="text-[11px] text-stone-500">
                        {{ $isDhaka ? 'Direct City Express Courier inside Dhaka.' : 'Nationwide Doorstep Delivery via Steadfast Courier.' }}
                    </p>
                </div>
            </div>
            <span class="px-3 py-1 rounded-full bg-white border border-stone-200 text-stone-600 font-bold text-[11px] shrink-0">
                Cash on Delivery
            </span>
        </div>
    </div>

    <!-- ORDER DETAILS & RECEIPT -->
    <div class="mt-8 space-y-6">

        <!-- 3-Column Info Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

            <!-- Customer -->
            <div class="bg-white rounded-2xl border border-stone-200 p-5 shadow-xs space-y-2">
                <span class="text-[10px] font-bold uppercase tracking-wider text-stone-400">Customer Contact</span>
                <p class="text-sm font-bold text-slate-900">{{ $order->customer_name }}</p>
                <p class="text-xs text-stone-600 font-mono">📞 {{ $order->customer_phone }}</p>
                @if($order->customer_email)
                    <p class="text-xs text-stone-600 truncate">✉️ {{ $order->customer_email }}</p>
                @endif
            </div>

            <!-- Shipping Destination -->
            <div class="bg-white rounded-2xl border border-stone-200 p-5 shadow-xs space-y-2">
                <span class="text-[10px] font-bold uppercase tracking-wider text-stone-400">Shipping Address</span>
                <p class="text-xs font-semibold text-slate-900">{{ $order->shipping_address }}</p>
                <p class="text-xs text-stone-600">{{ $order->shipping_thana }}, {{ $order->shipping_district }}</p>
                <p class="text-[11px] text-stone-400">{{ $order->shipping_division }} Division, Bangladesh</p>
            </div>

            <!-- Payment -->
            <div class="bg-white rounded-2xl border border-stone-200 p-5 shadow-xs space-y-2">
                <span class="text-[10px] font-bold uppercase tracking-wider text-stone-400">Payment Summary</span>
                <p class="text-sm font-bold text-slate-900 flex items-center gap-1.5">
                    <span>💵</span>
                    <span>Cash on Delivery</span>
                </p>
                <p class="text-xs text-stone-600">
                    Amount to Pay: <strong class="text-rose-950 font-black text-sm">৳ {{ number_format($order->total) }}</strong>
                </p>
                <p class="text-[10px] text-emerald-700 font-medium">Please inspect shoes before paying.</p>
            </div>

        </div>

        <!-- Itemized Table Card -->
        <div class="bg-white rounded-3xl border border-stone-200 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-stone-100 flex items-center justify-between">
                <h3 class="text-sm font-extrabold text-slate-900 font-serif">
                    Ordered Footwear Items ({{ $order->items->sum('quantity') }} pairs)
                </h3>
                <span class="text-xs text-stone-400">All prices in BDT (৳)</span>
            </div>

            <div class="divide-y divide-stone-100 p-5 space-y-4 sm:space-y-0">
                @foreach($order->items as $item)
                    <div class="flex items-center justify-between gap-4 py-3 first:pt-0 last:pb-0">
                        <div class="flex items-center gap-3.5 min-w-0">
                            <div class="w-16 h-16 rounded-2xl overflow-hidden bg-stone-100 border border-stone-200 shrink-0">
                                <img
                                    src="{{ $item->product?->image_url ?? asset('images/banners/hero-heels.webp') }}"
                                    alt="{{ $item->product_name }}"
                                    class="w-full h-full object-cover"
                                >
                            </div>
                            <div class="space-y-0.5 min-w-0">
                                <h4 class="text-xs sm:text-sm font-extrabold text-slate-900 truncate">
                                    {{ $item->product_name }}
                                </h4>
                                <div class="flex items-center gap-2 text-xs text-stone-500">
                                    @if(is_array($item->variant_details))
                                        <span>{{ $item->variant_details['color'] ?? '' }}</span>
                                        <span>•</span>
                                        <span class="font-mono font-bold text-slate-800">EU {{ $item->variant_details['size'] ?? '' }}</span>
                                    @endif
                                </div>
                                <p class="text-[11px] text-stone-400 font-medium">
                                    ৳ {{ number_format($item->unit_price) }} × {{ $item->quantity }}
                                </p>
                            </div>
                        </div>

                        <div class="text-right shrink-0">
                            <span class="text-sm sm:text-base font-black text-slate-950 font-serif">
                                ৳ {{ number_format($item->total_price) }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Receipt Financial Breakdown -->
            <div class="p-5 border-t border-stone-200 bg-stone-50/70 space-y-2.5 text-xs text-stone-600">
                <div class="flex items-center justify-between">
                    <span>Subtotal</span>
                    <span class="font-bold text-slate-900 font-serif text-sm">৳ {{ number_format($order->subtotal) }}</span>
                </div>

                <div class="flex items-center justify-between">
                    <span>Delivery Charge ({{ $isDhaka ? 'Inside Dhaka' : 'Outside Dhaka' }})</span>
                    <span class="font-bold font-serif {{ $order->delivery_charge == 0 ? 'text-emerald-700' : 'text-slate-900' }}">
                        {{ $order->delivery_charge == 0 ? 'FREE' : '৳ ' . number_format($order->delivery_charge) }}
                    </span>
                </div>

                @if($order->discount_amount > 0)
                    <div class="flex items-center justify-between text-emerald-700 font-semibold">
                        <span>Discount {{ $order->coupon_code ? "({$order->coupon_code})" : '' }}</span>
                        <span>- ৳ {{ number_format($order->discount_amount) }}</span>
                    </div>
                @endif

                <div class="pt-3 border-t border-stone-200 flex items-baseline justify-between text-slate-950">
                    <span class="text-sm font-extrabold font-serif">Grand Total</span>
                    <span class="text-xl sm:text-2xl font-black text-rose-950 font-serif">
                        ৳ {{ number_format($order->total) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- ACTION BUTTONS -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-2">
            <button
                type="button"
                onclick="window.print()"
                class="w-full sm:w-auto px-6 py-3 rounded-2xl bg-white border border-stone-300 text-slate-700 font-bold text-xs uppercase tracking-wider hover:bg-stone-50 transition shadow-xs flex items-center justify-center gap-2 cursor-pointer"
            >
                <span>🖨️</span>
                <span>Print Receipt</span>
            </button>

            <div class="flex items-center gap-3 w-full sm:w-auto">
                <a
                    href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $storeSettings['whatsapp_number'] ?? '+8801700000000') }}?text={{ urlencode('Hi Heel Bari! I just placed order #' . $order->order_number . '. Can you confirm my dispatch schedule?') }}"
                    target="_blank"
                    rel="noopener"
                    class="flex-1 sm:flex-none px-6 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs uppercase tracking-wider transition shadow-sm flex items-center justify-center gap-2"
                >
                    <span>💬 WhatsApp Us</span>
                </a>

                <a
                    href="{{ route('products.index') }}"
                    class="flex-1 sm:flex-none px-8 py-3 rounded-2xl bg-rose-900 hover:bg-rose-800 text-white font-bold text-xs uppercase tracking-wider transition shadow-md hover:shadow-lg flex items-center justify-center gap-2"
                >
                    <span>Continue Shopping →</span>
                </a>
            </div>
        </div>

    </div>

</div>
