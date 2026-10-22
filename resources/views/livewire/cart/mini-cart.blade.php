<div>
    <!-- BACKDROP & SLIDE-OVER DRAWER -->
    <div
        x-data="{ open: @entangle('isOpen') }"
        x-show="open"
        x-cloak
        class="fixed inset-0 z-50 overflow-hidden"
        aria-labelledby="slide-over-title"
        role="dialog"
        aria-modal="true"
        @keydown.window.escape="open = false; $wire.close()"
    >
        <!-- Background backdrop -->
        <div
            x-show="open"
            x-transition:enter="ease-in-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in-out duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs transition-opacity"
            @click="open = false; $wire.close()"
        ></div>

        <div class="fixed inset-y-0 right-0 max-w-full flex pl-10">
            <!-- Slide-over panel -->
            <div
                x-show="open"
                x-transition:enter="transform transition ease-in-out duration-300"
                x-transition:enter-start="translate-x-full"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transform transition ease-in-out duration-300"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="translate-x-full"
                class="w-screen max-w-md bg-white shadow-2xl flex flex-col justify-between"
            >
                <!-- TOP HEADER -->
                <div class="p-4 sm:p-5 border-b border-stone-200 bg-stone-50/80 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <span class="text-xl">🛍️</span>
                        <div>
                            <h2 class="text-base sm:text-lg font-extrabold text-slate-900 font-serif" id="slide-over-title">
                                Your Shopping Bag
                            </h2>
                            <p class="text-[11px] text-stone-500 font-medium">
                                {{ $calculation['items_count'] }} {{ \Illuminate\Support\Str::plural('pair', $calculation['items_count']) }} selected
                            </p>
                        </div>
                    </div>

                    <button
                        type="button"
                        @click="open = false; $wire.close()"
                        class="p-2 text-stone-400 hover:text-slate-800 rounded-full hover:bg-stone-200/60 transition"
                        aria-label="Close Shopping Bag Drawer"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- STOCK & CART NOTICES (IF REVALIDATION ADJUSTED ANYTHING) -->
                @if(!empty($notices))
                    <div class="px-4 pt-3 space-y-2">
                        @foreach($notices as $idx => $msg)
                            <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-900 flex items-start justify-between gap-2 shadow-xs">
                                <div class="flex items-start gap-2">
                                    <span class="text-amber-700 font-bold">⚠️</span>
                                    <span>{{ $msg }}</span>
                                </div>
                                <button type="button" wire:click="dismissNotice({{ $idx }})" class="text-amber-500 hover:text-amber-800 font-bold">✕</button>
                            </div>
                        @endforeach
                    </div>
                @endif

                <!-- FREE DELIVERY PROGRESS BAR -->
                @if($calculation['items_count'] > 0)
                    <div class="px-4 py-3 bg-rose-50/50 border-b border-rose-100">
                        @if($calculation['is_free_delivery'])
                            <div class="flex items-center gap-2 text-xs font-bold text-emerald-800">
                                <span>🎉</span>
                                <span>You have unlocked <strong>FREE Delivery</strong> across Bangladesh!</span>
                            </div>
                        @else
                            <div class="space-y-1.5">
                                <div class="flex items-center justify-between text-xs text-slate-700">
                                    <span>Add <strong class="text-rose-900 font-extrabold">৳ {{ number_format($calculation['amount_needed_for_free_delivery']) }}</strong> more for <strong>FREE Delivery</strong></span>
                                    <span class="font-bold text-rose-900">{{ $calculation['free_delivery_progress'] }}%</span>
                                </div>
                                <div class="w-full h-2 rounded-full bg-stone-200 overflow-hidden">
                                    <div
                                        class="h-full bg-gradient-to-r from-rose-800 to-rose-600 rounded-full transition-all duration-300"
                                        style="width: {{ $calculation['free_delivery_progress'] }}%;"
                                    ></div>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- CART ITEMS LIST (SCROLLABLE) -->
                <div class="flex-1 overflow-y-auto p-4 divide-y divide-stone-100 space-y-4">
                    @forelse($items as $item)
                        @php
                            $variant = $item->variant;
                            $product = $variant?->product;
                        @endphp
                        <div class="pt-4 first:pt-0 flex items-start gap-3.5 group" wire:key="mini-item-{{ $item->id }}">
                            <!-- Thumbnail -->
                            <a
                                href="{{ $product ? route('product.show', $product->slug) : '#' }}"
                                class="w-20 h-20 rounded-2xl overflow-hidden bg-stone-100 border border-stone-200 shrink-0 relative"
                            >
                                <img
                                    src="{{ $product?->image_url ?? asset('images/banners/hero-heels.webp') }}"
                                    alt="{{ $product?->name ?? 'Footwear' }}"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200"
                                    loading="lazy"
                                >
                            </a>

                            <!-- Details -->
                            <div class="flex-1 min-w-0 space-y-1">
                                <div class="flex items-start justify-between gap-2">
                                    <h3 class="text-xs sm:text-sm font-bold text-slate-900 leading-snug truncate">
                                        <a href="{{ $product ? route('product.show', $product->slug) : '#' }}" class="hover:text-rose-900 transition">
                                            {{ $product?->name ?? 'Footwear Pair' }}
                                        </a>
                                    </h3>

                                    <!-- Remove Button -->
                                    <button
                                        type="button"
                                        wire:click="removeItem({{ $item->id }})"
                                        wire:loading.attr="disabled"
                                        class="text-stone-400 hover:text-rose-800 transition p-1 -mr-1"
                                        title="Remove item"
                                        aria-label="Remove item"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>

                                <!-- Variant Badges (Color & Size) -->
                                <div class="flex items-center flex-wrap gap-1.5 text-[11px] text-stone-600">
                                    @if($variant)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-stone-100 font-medium">
                                            <span class="w-2 h-2 rounded-full border border-stone-300" style="background-color: {{ $variant->color_code }};"></span>
                                            {{ $variant->color }}
                                        </span>
                                        <span class="px-2 py-0.5 rounded-md bg-stone-100 font-mono font-bold text-slate-800">
                                            EU {{ $variant->size }}
                                        </span>
                                    @endif
                                </div>

                                <!-- Low Stock Alert Indicator -->
                                @if($variant && $variant->stock <= 3 && $variant->stock > 0)
                                    <p class="text-[10px] font-bold text-amber-700">
                                        ⚡ Only {{ $variant->stock }} left in stock!
                                    </p>
                                @endif

                                <!-- Price & Quantity Controls Row -->
                                <div class="flex items-center justify-between pt-1">
                                    <!-- Stepper -->
                                    <div class="inline-flex items-center rounded-xl border border-stone-200 bg-stone-50 overflow-hidden shadow-2xs">
                                        <button
                                            type="button"
                                            wire:click="decrement({{ $item->id }})"
                                            wire:loading.attr="disabled"
                                            class="w-7 h-7 flex items-center justify-center text-xs font-bold text-slate-700 hover:bg-stone-200 transition"
                                            aria-label="Decrease quantity"
                                        >
                                            −
                                        </button>
                                        <span class="w-8 text-center text-xs font-bold text-slate-900 font-mono">
                                            {{ $item->quantity }}
                                        </span>
                                        <button
                                            type="button"
                                            wire:click="increment({{ $item->id }})"
                                            wire:loading.attr="disabled"
                                            @if($variant && $item->quantity >= $variant->stock) disabled @endif
                                            class="w-7 h-7 flex items-center justify-center text-xs font-bold text-slate-700 hover:bg-stone-200 transition disabled:opacity-40 disabled:cursor-not-allowed"
                                            aria-label="Increase quantity"
                                        >
                                            +
                                        </button>
                                    </div>

                                    <!-- Price -->
                                    <div class="text-right">
                                        <p class="text-xs sm:text-sm font-extrabold text-slate-950 font-serif">
                                            ৳ {{ number_format($item->line_total) }}
                                        </p>
                                        @if($variant && $variant->has_discount)
                                            <p class="text-[10px] text-stone-400 line-through -mt-0.5">
                                                ৳ {{ number_format($variant->price * $item->quantity) }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <!-- EMPTY BAG STATE -->
                        <div class="h-full flex flex-col items-center justify-center text-center py-12 px-4 space-y-4">
                            <div class="w-20 h-20 rounded-full bg-rose-50 flex items-center justify-center text-4xl shadow-inner">
                                🛍️
                            </div>
                            <div class="space-y-1">
                                <h3 class="text-base font-extrabold text-slate-900 font-serif">Your shopping bag is empty</h3>
                                <p class="text-xs text-stone-500 max-w-xs">
                                    Explore our handcrafted block heels, cushioned flats, and festive Kolhapuri chappals.
                                </p>
                            </div>
                            <a
                                href="{{ route('products.index') }}"
                                @click="open = false; $wire.close()"
                                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-full bg-rose-900 hover:bg-rose-800 text-white font-bold text-xs uppercase tracking-wider shadow-md hover:shadow-lg transition"
                            >
                                <span>Shop Best Sellers</span>
                                <span>→</span>
                            </a>
                        </div>
                    @endforelse
                </div>

                <!-- FOOTER & CHECKOUT ACTIONS -->
                @if($calculation['items_count'] > 0)
                    <div class="p-4 sm:p-5 border-t border-stone-200 bg-stone-50/80 space-y-4">
                        <!-- Server-Calculated Subtotal -->
                        <div class="space-y-1.5">
                            <div class="flex items-baseline justify-between text-xs text-stone-500">
                                <span>Subtotal</span>
                                <span class="text-base font-black text-slate-950 font-serif">
                                    ৳ {{ number_format($calculation['subtotal']) }}
                                </span>
                            </div>
                            <p class="text-[11px] text-stone-500">
                                Shipping and discounts calculated on the next step.
                            </p>
                        </div>

                        <!-- Action Buttons -->
                        <div class="space-y-2">
                            <a
                                href="{{ route('cart.index') }}"
                                @click="open = false; $wire.close()"
                                class="w-full py-3 rounded-2xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs uppercase tracking-wider flex items-center justify-center gap-2 shadow-sm transition"
                            >
                                <span>View Full Bag & Estimate Shipping</span>
                                <span>→</span>
                            </a>

                            <a
                                href="{{ route('cart.index') }}"
                                @click="open = false; $wire.close()"
                                class="w-full py-3 rounded-2xl bg-rose-900 hover:bg-rose-800 text-white font-extrabold text-xs uppercase tracking-wider flex items-center justify-center gap-2 shadow-md hover:shadow-lg transition"
                            >
                                <span>Proceed to Checkout (৳ {{ number_format($calculation['subtotal']) }})</span>
                            </a>
                        </div>

                        <!-- Trust Guarantees -->
                        <div class="grid grid-cols-2 gap-2 pt-2 border-t border-stone-200/80 text-[10px] text-stone-600 font-medium text-center">
                            <div class="flex items-center justify-center gap-1.5 bg-white p-2 rounded-xl border border-stone-200/70">
                                <span>💵</span>
                                <span>Cash on Delivery</span>
                            </div>
                            <div class="flex items-center justify-center gap-1.5 bg-white p-2 rounded-xl border border-stone-200/70">
                                <span>🔄</span>
                                <span>7 Days Size Swap</span>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
