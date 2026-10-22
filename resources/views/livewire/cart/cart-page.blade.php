<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">

    <!-- BREADCRUMB -->
    <nav class="flex items-center text-xs text-stone-500 mb-6 space-x-2" aria-label="Breadcrumb">
        <a href="{{ route('home') }}" class="hover:text-rose-950 transition">Home</a>
        <span>/</span>
        <a href="{{ route('products.index') }}" class="hover:text-rose-950 transition">Catalog</a>
        <span>/</span>
        <span class="font-bold text-slate-900">Shopping Bag</span>
    </nav>

    <!-- PAGE TITLE -->
    <div class="flex items-baseline justify-between pb-6 mb-8 border-b border-stone-200">
        <div>
            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-slate-900 font-serif">
                Your Shopping Bag
            </h1>
            <p class="text-xs sm:text-sm text-stone-500 mt-1">
                Handcrafted footwear made with cloud-cushion footbeds for Bangladeshi women.
            </p>
        </div>

        <span class="inline-flex items-center px-3.5 py-1.5 rounded-full bg-rose-50 border border-rose-200 text-rose-950 font-bold text-xs">
            {{ $calculation['items_count'] }} {{ \Illuminate\Support\Str::plural('pair', $calculation['items_count']) }}
        </span>
    </div>

    <!-- STOCK ADJUSTMENT / OUT-OF-STOCK NOTICES -->
    @if(!empty($notices))
        <div class="mb-8 space-y-3">
            @foreach($notices as $idx => $msg)
                <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-xs sm:text-sm text-amber-950 flex items-start justify-between gap-3 shadow-xs">
                    <div class="flex items-start gap-2.5">
                        <span class="text-lg text-amber-700">⚠️</span>
                        <div>
                            <p class="font-bold">Inventory Notice</p>
                            <p class="text-amber-900">{{ $msg }}</p>
                        </div>
                    </div>
                    <button type="button" wire:click="dismissNotice({{ $idx }})" class="text-amber-500 hover:text-amber-900 font-bold text-base">✕</button>
                </div>
            @endforeach
        </div>
    @endif

    @if($calculation['items_count'] === 0)
        <!-- EMPTY CART STATE -->
        <div class="bg-white rounded-3xl border border-stone-200 p-8 sm:p-16 text-center max-w-2xl mx-auto shadow-sm space-y-6">
            <div class="w-24 h-24 rounded-full bg-rose-50 flex items-center justify-center text-5xl mx-auto shadow-inner">
                🛍️
            </div>
            <div class="space-y-2">
                <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 font-serif">Your shopping bag is completely empty</h2>
                <p class="text-xs sm:text-sm text-stone-500 max-w-md mx-auto">
                    You haven't added any pairs to your bag yet. Explore our handcrafted heels, daily flats, slip-on mules, and Boston clogs.
                </p>
            </div>
            <div class="pt-2">
                <a
                    href="{{ route('products.index') }}"
                    class="inline-flex items-center gap-2.5 px-8 py-3.5 rounded-full bg-rose-900 hover:bg-rose-800 text-white font-extrabold text-xs uppercase tracking-wider shadow-lg hover:shadow-rose-900/30 transition transform hover:-translate-y-0.5"
                >
                    <span>Browse All Footwear</span>
                    <span>→</span>
                </a>
            </div>
        </div>
    @else
        <!-- MAIN 2-COLUMN LAYOUT -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start">

            <!-- LEFT COLUMN: ITEMS LIST & DELIVERY ZONE SELECTOR (8 cols on lg) -->
            <div class="lg:col-span-8 space-y-6">

                <!-- ITEMS TABLE / LIST -->
                <div class="bg-white rounded-3xl border border-stone-200 shadow-sm overflow-hidden">
                    <div class="p-4 sm:p-6 border-b border-stone-100 flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-stone-400">Bag Items ({{ $calculation['items_count'] }})</span>
                        <button
                            type="button"
                            wire:click="clearCart"
                            wire:confirm="Are you sure you want to empty your shopping bag?"
                            class="text-xs font-semibold text-rose-800 hover:text-rose-950 transition"
                        >
                            Clear Bag
                        </button>
                    </div>

                    <div class="divide-y divide-stone-100 p-4 sm:p-6 space-y-6 sm:space-y-0">
                        @foreach($items as $item)
                            @php
                                $variant = $item->variant;
                                $product = $variant?->product;
                            @endphp
                            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 py-4 first:pt-0 last:pb-0" wire:key="cart-item-{{ $item->id }}">

                                <!-- Item Info & Image -->
                                <div class="flex items-center gap-4 flex-1 min-w-0">
                                    <a
                                        href="{{ $product ? route('product.show', $product->slug) : '#' }}"
                                        class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl overflow-hidden bg-stone-100 border border-stone-200 shrink-0 relative group"
                                    >
                                        <img
                                            src="{{ $product?->image_url ?? asset('images/banners/hero-heels.webp') }}"
                                            alt="{{ $product?->name ?? 'Footwear' }}"
                                            class="w-full h-full object-cover group-hover:scale-105 transition-transform"
                                            loading="lazy"
                                        >
                                    </a>

                                    <div class="space-y-1 min-w-0">
                                        <span class="text-[10px] font-bold uppercase tracking-widest text-rose-800">
                                            {{ $product?->category?->name ?? 'Women Footwear' }}
                                        </span>
                                        <h3 class="text-sm sm:text-base font-extrabold text-slate-900 font-serif leading-snug truncate">
                                            <a href="{{ $product ? route('product.show', $product->slug) : '#' }}" class="hover:text-rose-900 transition">
                                                {{ $product?->name ?? 'Footwear' }}
                                            </a>
                                        </h3>

                                        <div class="flex items-center flex-wrap gap-2 text-xs text-stone-600">
                                            @if($variant)
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md bg-stone-100 font-medium">
                                                    <span class="w-2.5 h-2.5 rounded-full border border-stone-300" style="background-color: {{ $variant->color_code }};"></span>
                                                    {{ $variant->color }}
                                                </span>
                                                <span class="px-2.5 py-0.5 rounded-md bg-stone-100 font-mono font-bold text-slate-800">
                                                    EU {{ $variant->size }}
                                                </span>
                                                <span class="text-[10px] text-stone-400 font-mono">
                                                    SKU: {{ $variant->sku }}
                                                </span>
                                            @endif
                                        </div>

                                        <!-- Stock status -->
                                        @if($variant && $variant->stock <= 3 && $variant->stock > 0)
                                            <p class="text-[11px] font-bold text-amber-700">
                                                ⚡ Only {{ $variant->stock }} pairs remaining in stock!
                                            </p>
                                        @endif
                                    </div>
                                </div>

                                <!-- Quantity Stepper + Line Price + Remove -->
                                <div class="flex items-center justify-between sm:justify-end gap-6 w-full sm:w-auto pt-2 sm:pt-0 border-t sm:border-t-0 border-stone-100">
                                    <!-- Stepper -->
                                    <div class="inline-flex items-center rounded-2xl border border-stone-200 bg-stone-50 overflow-hidden shadow-2xs">
                                        <button
                                            type="button"
                                            wire:click="decrement({{ $item->id }})"
                                            wire:loading.attr="disabled"
                                            class="w-8 h-8 flex items-center justify-center text-sm font-bold text-slate-700 hover:bg-stone-200 transition"
                                            aria-label="Decrease quantity"
                                        >
                                            −
                                        </button>
                                        <span class="w-9 text-center text-xs font-bold text-slate-900 font-mono">
                                            {{ $item->quantity }}
                                        </span>
                                        <button
                                            type="button"
                                            wire:click="increment({{ $item->id }})"
                                            wire:loading.attr="disabled"
                                            @if($variant && $item->quantity >= $variant->stock) disabled @endif
                                            class="w-8 h-8 flex items-center justify-center text-sm font-bold text-slate-700 hover:bg-stone-200 transition disabled:opacity-40 disabled:cursor-not-allowed"
                                            aria-label="Increase quantity"
                                        >
                                            +
                                        </button>
                                    </div>

                                    <!-- Price -->
                                    <div class="text-right min-w-[90px]">
                                        <p class="text-base sm:text-lg font-black text-slate-950 font-serif">
                                            ৳ {{ number_format($item->line_total) }}
                                        </p>
                                        <p class="text-[11px] text-stone-500 font-medium">
                                            ৳ {{ number_format($variant ? $variant->effective_price : 0) }} / pair
                                        </p>
                                    </div>

                                    <!-- Remove -->
                                    <button
                                        type="button"
                                        wire:click="removeItem({{ $item->id }})"
                                        wire:loading.attr="disabled"
                                        class="p-2 text-stone-400 hover:text-rose-800 rounded-lg hover:bg-rose-50 transition"
                                        title="Remove item"
                                        aria-label="Remove item"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>

                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- ESTIMATE DELIVERY CHARGES BY REGION -->
                <div class="bg-white rounded-3xl border border-stone-200 p-6 shadow-sm space-y-4">
                    <div class="flex items-center gap-2 pb-3 border-b border-stone-100">
                        <span class="text-xl">📍</span>
                        <div>
                            <h3 class="text-sm font-extrabold text-slate-900 font-serif">Select Delivery Destination</h3>
                            <p class="text-xs text-stone-500">Delivery fees and timeline are computed automatically on the server.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <!-- Inside Dhaka -->
                        <label
                            class="relative flex items-center justify-between p-4 rounded-2xl border-2 cursor-pointer transition select-none {{ $deliveryZone === 'inside_dhaka' ? 'border-rose-900 bg-rose-50/40 ring-1 ring-rose-900/20' : 'border-stone-200 hover:border-stone-300 bg-white' }}"
                        >
                            <div class="flex items-center gap-3">
                                <input
                                    type="radio"
                                    name="delivery_zone"
                                    value="inside_dhaka"
                                    wire:click="setDeliveryZone('inside_dhaka')"
                                    @checked($deliveryZone === 'inside_dhaka')
                                    class="w-4 h-4 text-rose-900 focus:ring-rose-800"
                                >
                                <div>
                                    <p class="text-xs sm:text-sm font-extrabold text-slate-900">Inside Dhaka</p>
                                    <p class="text-[11px] text-stone-500">24 – 48 Hours Delivery</p>
                                </div>
                            </div>
                            <span class="text-xs font-black {{ $calculation['is_free_delivery'] ? 'text-emerald-700' : 'text-slate-900' }}">
                                {{ $calculation['is_free_delivery'] ? 'FREE' : '৳ 70' }}
                            </span>
                        </label>

                        <!-- Outside Dhaka -->
                        <label
                            class="relative flex items-center justify-between p-4 rounded-2xl border-2 cursor-pointer transition select-none {{ $deliveryZone === 'outside_dhaka' ? 'border-rose-900 bg-rose-50/40 ring-1 ring-rose-900/20' : 'border-stone-200 hover:border-stone-300 bg-white' }}"
                        >
                            <div class="flex items-center gap-3">
                                <input
                                    type="radio"
                                    name="delivery_zone"
                                    value="outside_dhaka"
                                    wire:click="setDeliveryZone('outside_dhaka')"
                                    @checked($deliveryZone === 'outside_dhaka')
                                    class="w-4 h-4 text-rose-900 focus:ring-rose-800"
                                >
                                <div>
                                    <p class="text-xs sm:text-sm font-extrabold text-slate-900">Outside Dhaka (All 64 Districts)</p>
                                    <p class="text-[11px] text-stone-500">3 – 5 Business Days via Steadfast</p>
                                </div>
                            </div>
                            <span class="text-xs font-black {{ $calculation['is_free_delivery'] ? 'text-emerald-700' : 'text-slate-900' }}">
                                {{ $calculation['is_free_delivery'] ? 'FREE' : '৳ 130' }}
                            </span>
                        </label>
                    </div>
                </div>

                <!-- SERVICE GUARANTEES -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="p-4 rounded-2xl bg-stone-100/70 border border-stone-200 flex items-center gap-3">
                        <span class="text-2xl">💵</span>
                        <div>
                            <p class="text-xs font-bold text-slate-900">Cash on Delivery</p>
                            <p class="text-[11px] text-stone-500">Inspect shoes before paying</p>
                        </div>
                    </div>

                    <div class="p-4 rounded-2xl bg-stone-100/70 border border-stone-200 flex items-center gap-3">
                        <span class="text-2xl">🔄</span>
                        <div>
                            <p class="text-xs font-bold text-slate-900">7 Days Size Swap</p>
                            <p class="text-[11px] text-stone-500">Doorstep exchange in 64 districts</p>
                        </div>
                    </div>

                    <div class="p-4 rounded-2xl bg-stone-100/70 border border-stone-200 flex items-center gap-3">
                        <span class="text-2xl">☁️</span>
                        <div>
                            <p class="text-xs font-bold text-slate-900">Cloud Insole Cushion</p>
                            <p class="text-[11px] text-stone-500">Pain-free all-day comfort</p>
                        </div>
                    </div>
                </div>

            </div>

            <!-- RIGHT COLUMN: STICKY ORDER SUMMARY (4 cols on lg) -->
            <div class="lg:col-span-4 sticky top-28 space-y-6">

                <div class="bg-white rounded-3xl border border-stone-200 p-6 sm:p-7 shadow-xl space-y-6">

                    <!-- Free Delivery Progress Meter -->
                    <div class="space-y-2 pb-5 border-b border-stone-100">
                        @if($calculation['is_free_delivery'])
                            <div class="p-3 rounded-2xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-900 font-bold flex items-center gap-2">
                                <span>🎉</span>
                                <span>You have unlocked <strong>FREE Home Delivery</strong>!</span>
                            </div>
                        @else
                            <div class="flex items-center justify-between text-xs text-slate-700">
                                <span>Add <strong class="text-rose-900 font-extrabold">৳ {{ number_format($calculation['amount_needed_for_free_delivery']) }}</strong> for <strong>FREE Delivery</strong></span>
                                <span class="font-bold text-rose-900">{{ $calculation['free_delivery_progress'] }}%</span>
                            </div>
                            <div class="w-full h-2.5 rounded-full bg-stone-100 overflow-hidden">
                                <div
                                    class="h-full bg-gradient-to-r from-rose-900 to-rose-600 rounded-full transition-all duration-300"
                                    style="width: {{ $calculation['free_delivery_progress'] }}%;"
                                ></div>
                            </div>
                        @endif
                    </div>

                    <!-- Order Summary Breakdown -->
                    <div class="space-y-3.5 text-xs text-stone-600">
                        <div class="flex items-center justify-between">
                            <span>Subtotal ({{ $calculation['items_count'] }} pairs)</span>
                            <span class="font-bold text-slate-900 font-serif text-sm">৳ {{ number_format($calculation['subtotal']) }}</span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span>Estimated Delivery Fee ({{ $deliveryZone === 'outside_dhaka' ? 'Outside Dhaka' : 'Inside Dhaka' }})</span>
                            <span class="font-bold font-serif {{ $calculation['delivery_fee'] == 0 ? 'text-emerald-700' : 'text-slate-900' }}">
                                {{ $calculation['delivery_fee'] == 0 ? 'FREE' : '৳ ' . number_format($calculation['delivery_fee']) }}
                            </span>
                        </div>

                        @if($calculation['coupon_code'])
                            <div class="flex items-center justify-between text-emerald-700 font-semibold">
                                <span class="flex items-center gap-1">
                                    <span>🎟️ Promo: {{ $calculation['coupon_code'] }}</span>
                                    <button type="button" wire:click="removeCoupon" class="text-stone-400 hover:text-rose-800 ml-1">✕</button>
                                </span>
                                <span>- ৳ {{ number_format($calculation['coupon_discount']) }}</span>
                            </div>
                        @endif

                        <div class="pt-4 border-t border-stone-200 flex items-baseline justify-between">
                            <div>
                                <span class="text-base font-extrabold text-slate-950 font-serif">Grand Total</span>
                                <p class="text-[10px] text-stone-400">All prices in BDT (৳) • VAT Included</p>
                            </div>
                            <span class="text-2xl sm:text-3xl font-black text-rose-950 font-serif">
                                ৳ {{ number_format($calculation['grand_total']) }}
                            </span>
                        </div>
                    </div>

                    <!-- Promo Coupon Input -->
                    <div class="pt-2">
                        @if(! $calculation['coupon_code'])
                            <form wire:submit.prevent="applyCoupon" class="space-y-2">
                                <div class="flex items-center gap-2">
                                    <input
                                        type="text"
                                        wire:model="couponInput"
                                        placeholder="Enter coupon code (e.g. EID20)"
                                        class="flex-1 px-4 py-2.5 rounded-xl border border-stone-200 text-xs text-slate-900 uppercase font-mono tracking-wider focus:outline-hidden focus:ring-2 focus:ring-rose-800"
                                    >
                                    <button
                                        type="submit"
                                        class="px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs uppercase tracking-wider transition"
                                    >
                                        Apply
                                    </button>
                                </div>
                                @if($couponMessage)
                                    <p class="text-[11px] font-medium {{ $couponSuccess ? 'text-emerald-700' : 'text-rose-700' }}">
                                        {{ $couponMessage }}
                                    </p>
                                @endif
                            </form>
                        @endif
                    </div>

                    <!-- Checkout Primary Button -->
                    <div class="space-y-3 pt-2">
                        <button
                            type="button"
                            class="w-full py-4 rounded-2xl bg-rose-900 hover:bg-rose-800 text-white font-black text-sm uppercase tracking-wider shadow-xl hover:shadow-rose-900/30 transition-all flex items-center justify-center gap-2"
                        >
                            <span>Proceed to Checkout</span>
                            <span>→</span>
                        </button>

                        <a
                            href="https://wa.me/8801700000000?text={{ urlencode('Hi [BRAND_NAME]! I want to complete my order of ' . $calculation['items_count'] . ' pairs for BDT ' . $calculation['grand_total']) }}"
                            target="_blank"
                            rel="noopener"
                            class="w-full py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs uppercase tracking-wider flex items-center justify-center gap-2 transition shadow-sm"
                        >
                            <span>💬 Order via WhatsApp</span>
                        </a>
                    </div>

                    <!-- Payment Badges in Summary -->
                    <div class="pt-4 border-t border-stone-100 flex items-center justify-center flex-wrap gap-2 text-[10px] text-stone-400">
                        <span>bKash</span>
                        <span>•</span>
                        <span>Nagad</span>
                        <span>•</span>
                        <span>Rocket</span>
                        <span>•</span>
                        <span>VISA</span>
                        <span>•</span>
                        <span>Mastercard</span>
                        <span>•</span>
                        <span>Cash on Delivery</span>
                    </div>

                </div>

                <!-- Back to Shopping CTA -->
                <div class="text-center">
                    <a href="{{ route('products.index') }}" class="text-xs font-semibold text-rose-900 hover:underline">
                        ← Continue Shopping for Footwear
                    </a>
                </div>

            </div>

        </div>
    @endif

</div>
