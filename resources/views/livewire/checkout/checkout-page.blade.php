<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">

    <!-- BREADCRUMB -->
    <nav class="flex items-center text-xs text-stone-500 mb-6 space-x-2" aria-label="Breadcrumb">
        <a href="{{ route('home') }}" class="hover:text-rose-950 transition">Home</a>
        <span>/</span>
        <a href="{{ route('cart.index') }}" class="hover:text-rose-950 transition">Shopping Bag</a>
        <span>/</span>
        <span class="font-bold text-slate-900">Checkout</span>
    </nav>

    <!-- PAGE HEADER -->
    <div class="pb-6 mb-8 border-b border-stone-200">
        <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-slate-900 font-serif">
            Express Checkout
        </h1>
        <p class="text-xs sm:text-sm text-stone-500 mt-1">
            Guest checkout enabled — no account required. Cash on Delivery across all 64 districts in Bangladesh.
        </p>
    </div>

    <!-- ERROR & STOCK NOTICES BANNER -->
    @if($submissionError)
        <div class="mb-8 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-xs sm:text-sm text-rose-900 flex items-start gap-3 shadow-xs">
            <span class="text-lg text-rose-700">❌</span>
            <div>
                <p class="font-bold">Order Could Not Be Placed</p>
                <p>{{ $submissionError }}</p>
            </div>
        </div>
    @endif

    @if(!empty($stockNotices))
        <div class="mb-8 space-y-2">
            @foreach($stockNotices as $msg)
                <div class="p-3.5 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-900 flex items-center gap-2.5 shadow-2xs">
                    <span class="text-base text-amber-700">⚠️</span>
                    <span>{{ $msg }}</span>
                </div>
            @endforeach
        </div>
    @endif

    <!-- 2-COLUMN CHECKOUT GRID -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start">

        <!-- LEFT COLUMN: CHECKOUT FORM (7 cols on lg) -->
        <div class="lg:col-span-7 space-y-8">

            <form wire:submit.prevent="placeOrder" class="space-y-8">

                <!-- 1. CUSTOMER CONTACT INFO -->
                <div class="bg-white rounded-3xl border border-stone-200 p-6 sm:p-7 shadow-sm space-y-5">
                    <div class="flex items-center gap-2.5 pb-4 border-b border-stone-100">
                        <span class="w-7 h-7 rounded-full bg-rose-900 text-white font-bold text-xs flex items-center justify-center">1</span>
                        <h2 class="text-base sm:text-lg font-extrabold text-slate-900 font-serif">
                            Customer Contact Information
                        </h2>
                    </div>

                    <div class="space-y-4">
                        <!-- Full Name -->
                        <div>
                            <label for="customer_name" class="block text-xs font-bold text-slate-800 uppercase tracking-wide mb-1.5">
                                Full Name <span class="text-rose-700">*</span>
                            </label>
                            <input
                                type="text"
                                id="customer_name"
                                wire:model.defer="customer_name"
                                placeholder="e.g. Nusrat Jahan"
                                class="w-full px-4 py-3 rounded-2xl border border-stone-300 text-sm focus:border-rose-900 focus:ring-2 focus:ring-rose-900/20 transition @error('customer_name') border-rose-500 bg-rose-50/20 @enderror"
                            >
                            @error('customer_name')
                                <p class="text-xs text-rose-700 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Phone Number -->
                        <div>
                            <label for="customer_phone" class="block text-xs font-bold text-slate-800 uppercase tracking-wide mb-1.5">
                                Mobile Number <span class="text-rose-700">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-xs font-bold text-stone-500">
                                    🇧🇩 +880
                                </span>
                                <input
                                    type="tel"
                                    id="customer_phone"
                                    wire:model.defer="customer_phone"
                                    placeholder="017XXXXXXXX"
                                    class="w-full pl-20 pr-4 py-3 rounded-2xl border border-stone-300 text-sm font-mono focus:border-rose-900 focus:ring-2 focus:ring-rose-900/20 transition @error('customer_phone') border-rose-500 bg-rose-50/20 @enderror"
                                >
                            </div>
                            <p class="text-[11px] text-stone-500 mt-1">
                                We will send SMS updates and call you before delivery.
                            </p>
                            @error('customer_phone')
                                <p class="text-xs text-rose-700 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Email (Optional) -->
                        <div>
                            <label for="customer_email" class="block text-xs font-bold text-slate-800 uppercase tracking-wide mb-1.5">
                                Email Address <span class="text-stone-400 font-normal lowercase">(optional)</span>
                            </label>
                            <input
                                type="email"
                                id="customer_email"
                                wire:model.defer="customer_email"
                                placeholder="nusrat@example.com"
                                class="w-full px-4 py-3 rounded-2xl border border-stone-300 text-sm focus:border-rose-900 focus:ring-2 focus:ring-rose-900/20 transition @error('customer_email') border-rose-500 bg-rose-50/20 @enderror"
                            >
                            @error('customer_email')
                                <p class="text-xs text-rose-700 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- 2. SHIPPING & DELIVERY ADDRESS -->
                <div class="bg-white rounded-3xl border border-stone-200 p-6 sm:p-7 shadow-sm space-y-5">
                    <div class="flex items-center gap-2.5 pb-4 border-b border-stone-100">
                        <span class="w-7 h-7 rounded-full bg-rose-900 text-white font-bold text-xs flex items-center justify-center">2</span>
                        <div>
                            <h2 class="text-base sm:text-lg font-extrabold text-slate-900 font-serif">
                                Delivery Address (Bangladesh)
                            </h2>
                            <p class="text-[11px] text-stone-500">
                                Delivery fee updates automatically based on your destination district.
                            </p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <!-- Division & District Row -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Division -->
                            <div>
                                <label for="shipping_division" class="block text-xs font-bold text-slate-800 uppercase tracking-wide mb-1.5">
                                    Division <span class="text-rose-700">*</span>
                                </label>
                                <select
                                    id="shipping_division"
                                    wire:model.live="shipping_division"
                                    class="w-full px-4 py-3 rounded-2xl border border-stone-300 text-sm bg-white focus:border-rose-900 focus:ring-2 focus:ring-rose-900/20 transition @error('shipping_division') border-rose-500 @enderror"
                                >
                                    @foreach($divisions as $div)
                                        <option value="{{ $div }}">{{ $div }}</option>
                                    @endforeach
                                </select>
                                @error('shipping_division')
                                    <p class="text-xs text-rose-700 mt-1 font-medium">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- District -->
                            <div>
                                <label for="shipping_district" class="block text-xs font-bold text-slate-800 uppercase tracking-wide mb-1.5">
                                    District <span class="text-rose-700">*</span>
                                </label>
                                <select
                                    id="shipping_district"
                                    wire:model.live="shipping_district"
                                    class="w-full px-4 py-3 rounded-2xl border border-stone-300 text-sm bg-white focus:border-rose-900 focus:ring-2 focus:ring-rose-900/20 transition @error('shipping_district') border-rose-500 @enderror"
                                >
                                    @foreach($districts as $dist)
                                        <option value="{{ $dist }}">
                                            {{ $dist }} {{ strcasecmp($dist, 'Dhaka') === 0 ? '(Inside Dhaka • ৳70)' : '(Outside Dhaka • ৳130)' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('shipping_district')
                                    <p class="text-xs text-rose-700 mt-1 font-medium">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Thana / Area (with suggestions) -->
                        <div>
                            <label for="shipping_thana" class="block text-xs font-bold text-slate-800 uppercase tracking-wide mb-1.5">
                                Thana / Upazila / Area <span class="text-rose-700">*</span>
                            </label>
                            <input
                                type="text"
                                id="shipping_thana"
                                list="thana_suggestions"
                                wire:model.defer="shipping_thana"
                                placeholder="{{ $isDhaka ? 'e.g. Dhanmondi, Gulshan, Uttara, Mirpur' : 'e.g. Kotwali, Agrabad, Boalia' }}"
                                class="w-full px-4 py-3 rounded-2xl border border-stone-300 text-sm focus:border-rose-900 focus:ring-2 focus:ring-rose-900/20 transition @error('shipping_thana') border-rose-500 bg-rose-50/20 @enderror"
                            >
                            @if($isDhaka)
                                <datalist id="thana_suggestions">
                                    @foreach($dhakaThanaSuggestions as $thana)
                                        <option value="{{ $thana }}"></option>
                                    @endforeach
                                </datalist>
                            @endif
                            @error('shipping_thana')
                                <p class="text-xs text-rose-700 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Detailed Street Address -->
                        <div>
                            <label for="shipping_address" class="block text-xs font-bold text-slate-800 uppercase tracking-wide mb-1.5">
                                Detailed Street Address <span class="text-rose-700">*</span>
                            </label>
                            <textarea
                                id="shipping_address"
                                wire:model.defer="shipping_address"
                                rows="3"
                                placeholder="House Number, Road Number, Flat / Apartment, Landmark (e.g. House 42, Road 27, Flat 4B, Near Star Kabab)"
                                class="w-full px-4 py-3 rounded-2xl border border-stone-300 text-sm focus:border-rose-900 focus:ring-2 focus:ring-rose-900/20 transition @error('shipping_address') border-rose-500 bg-rose-50/20 @enderror"
                            ></textarea>
                            @error('shipping_address')
                                <p class="text-xs text-rose-700 mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Special Delivery Notes (Optional) -->
                        <div>
                            <label for="notes" class="block text-xs font-bold text-slate-800 uppercase tracking-wide mb-1.5">
                                Special Order / Delivery Instructions <span class="text-stone-400 font-normal lowercase">(optional)</span>
                            </label>
                            <input
                                type="text"
                                id="notes"
                                wire:model.defer="notes"
                                placeholder="e.g. Call before arriving, or please deliver after 3 PM"
                                class="w-full px-4 py-3 rounded-2xl border border-stone-300 text-sm focus:border-rose-900 focus:ring-2 focus:ring-rose-900/20 transition"
                            >
                        </div>
                    </div>
                </div>

                <!-- 3. PAYMENT METHOD (BEHIND ABSTRACTION) -->
                <div class="bg-white rounded-3xl border border-stone-200 p-6 sm:p-7 shadow-sm space-y-5">
                    <div class="flex items-center gap-2.5 pb-4 border-b border-stone-100">
                        <span class="w-7 h-7 rounded-full bg-rose-900 text-white font-bold text-xs flex items-center justify-center">3</span>
                        <h2 class="text-base sm:text-lg font-extrabold text-slate-900 font-serif">
                            Payment Method
                        </h2>
                    </div>

                    <div class="space-y-3">
                        <!-- Cash on Delivery (Active) -->
                        <label
                            class="relative flex items-start gap-4 p-4 rounded-2xl border-2 border-rose-900 bg-rose-50/40 cursor-pointer shadow-xs select-none"
                        >
                            <input
                                type="radio"
                                name="payment_method"
                                value="cod"
                                wire:model="payment_method"
                                checked
                                class="w-4 h-4 text-rose-900 mt-1 focus:ring-rose-800"
                            >
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                                        <span>💵</span>
                                        <span>Cash on Delivery (COD)</span>
                                    </span>
                                    <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-bold text-[10px] uppercase">
                                        Recommended
                                    </span>
                                </div>
                                <p class="text-xs text-stone-600 mt-1">
                                    Pay cash directly to our delivery courier after inspecting the footwear at your doorstep. Zero risk, 100% peace of mind.
                                </p>
                            </div>
                        </label>

                        <!-- bKash / Nagad / Online Cards (Extensible Placeholder) -->
                        <div
                            class="relative flex items-start gap-4 p-4 rounded-2xl border border-stone-200 bg-stone-50/60 opacity-75"
                        >
                            <div class="w-4 h-4 rounded-full border border-stone-300 mt-1 bg-stone-200"></div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-bold text-slate-700 flex items-center gap-2">
                                        <span>💳</span>
                                        <span>bKash / Nagad / Debit & Credit Card</span>
                                    </span>
                                    <span class="px-2.5 py-0.5 rounded-full bg-stone-200 text-stone-600 font-bold text-[10px] uppercase">
                                        Coming Soon
                                    </span>
                                </div>
                                <p class="text-xs text-stone-500 mt-1">
                                    Direct online payment gateway integration is being onboarded. You can also pay via bKash upon courier arrival.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SUBMIT BUTTON (MOBILE-VISIBLE ONLY ON SMALL SCREENS) -->
                <div class="lg:hidden">
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        :disabled="$wire.isSubmitting"
                        class="w-full py-4 rounded-2xl bg-rose-900 hover:bg-rose-800 text-white font-extrabold text-base uppercase tracking-wider shadow-xl hover:shadow-rose-900/30 transition-all flex items-center justify-center gap-2.5 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                    >
                        <span wire:loading.remove wire:target="placeOrder">
                            🛍️ Confirm Order (৳ {{ number_format($calculation['grand_total']) }})
                        </span>
                        <span wire:loading wire:target="placeOrder" class="flex items-center gap-2">
                            <span class="animate-spin inline-block">⏳</span> Processing Order...
                        </span>
                    </button>
                </div>

            </form>

        </div>

        <!-- RIGHT COLUMN: STICKY ORDER SUMMARY (5 cols on lg) -->
        <div class="lg:col-span-5 sticky top-28 space-y-6">

            <div class="bg-white rounded-3xl border border-stone-200 p-6 sm:p-7 shadow-xl space-y-6">

                <!-- Header -->
                <div class="flex items-center justify-between pb-4 border-b border-stone-100">
                    <h2 class="text-base font-extrabold text-slate-900 font-serif">
                        Order Summary
                    </h2>
                    <span class="text-xs text-stone-500 font-semibold">
                        {{ $calculation['items_count'] }} {{ \Illuminate\Support\Str::plural('pair', $calculation['items_count']) }}
                    </span>
                </div>

                <!-- Item Mini Cards -->
                <div class="max-h-64 overflow-y-auto divide-y divide-stone-100 pr-1 space-y-3">
                    @foreach($items as $item)
                        @php
                            $variant = $item->variant;
                            $product = $variant?->product;
                        @endphp
                        <div class="pt-3 first:pt-0 flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-14 h-14 rounded-xl overflow-hidden bg-stone-100 border border-stone-200 shrink-0">
                                    <img
                                        src="{{ $product?->image_url ?? asset('images/banners/hero-heels.webp') }}"
                                        alt="{{ $product?->name ?? 'Footwear' }}"
                                        class="w-full h-full object-cover"
                                    >
                                </div>
                                <div class="min-w-0 space-y-0.5">
                                    <h4 class="text-xs font-bold text-slate-900 truncate">
                                        {{ $product?->name ?? 'Shoe' }}
                                    </h4>
                                    <p class="text-[11px] text-stone-500">
                                        {{ $variant?->color }} • EU {{ $variant?->size }} × {{ $item->quantity }}
                                    </p>
                                </div>
                            </div>
                            <span class="text-xs font-extrabold text-slate-950 font-serif shrink-0">
                                ৳ {{ number_format($item->line_total) }}
                            </span>
                        </div>
                    @endforeach
                </div>

                <!-- Free Delivery Progress Meter -->
                <div class="p-3.5 rounded-2xl bg-stone-50 border border-stone-100 space-y-1.5">
                    @if($calculation['is_free_delivery'])
                        <p class="text-xs font-bold text-emerald-800 flex items-center gap-1.5">
                            <span>🎉</span>
                            <span>Eligible for <strong>FREE Delivery</strong> across Bangladesh!</span>
                        </p>
                    @else
                        <div class="flex items-center justify-between text-xs text-slate-700">
                            <span>Add <strong>৳ {{ number_format($calculation['amount_needed_for_free_delivery']) }}</strong> more for <strong>FREE Delivery</strong></span>
                            <span class="font-bold text-rose-900">{{ $calculation['free_delivery_progress'] }}%</span>
                        </div>
                        <div class="w-full h-2 rounded-full bg-stone-200 overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-rose-800 to-rose-600 rounded-full" style="width: {{ $calculation['free_delivery_progress'] }}%;"></div>
                        </div>
                    @endif
                </div>

                <!-- Financial Calculation (100% Server Recalculated) -->
                <div class="space-y-3 text-xs text-stone-600 pt-2 border-t border-stone-100">
                    <div class="flex items-center justify-between">
                        <span>Items Subtotal</span>
                        <span class="font-bold text-slate-900 font-serif text-sm">৳ {{ number_format($calculation['subtotal']) }}</span>
                    </div>

                    <div class="flex items-center justify-between">
                        <div>
                            <span>Delivery Charge</span>
                            <span class="text-[10px] text-stone-400 block font-normal">
                                {{ $isDhaka ? 'Inside Dhaka (24–48h)' : 'Outside Dhaka (3–5 Days)' }}
                            </span>
                        </div>
                        <span class="font-bold font-serif {{ $calculation['delivery_fee'] == 0 ? 'text-emerald-700' : 'text-slate-900' }}">
                            {{ $calculation['delivery_fee'] == 0 ? 'FREE' : '৳ ' . number_format($calculation['delivery_fee']) }}
                        </span>
                    </div>

                    @if($calculation['coupon_code'])
                        <div class="flex items-center justify-between text-emerald-700 font-semibold">
                            <span class="flex items-center gap-1">
                                <span>🎟️ Coupon ({{ $calculation['coupon_code'] }})</span>
                                <button type="button" wire:click="removeCoupon" class="text-stone-400 hover:text-rose-800 ml-1">✕</button>
                            </span>
                            <span>- ৳ {{ number_format($calculation['coupon_discount']) }}</span>
                        </div>
                    @endif

                    <div class="pt-4 border-t border-stone-200 flex items-baseline justify-between">
                        <div>
                            <span class="text-base font-extrabold text-slate-950 font-serif">Grand Total</span>
                            <p class="text-[10px] text-stone-400">All prices in BDT (৳) • Cash on Delivery</p>
                        </div>
                        <span class="text-2xl sm:text-3xl font-black text-rose-950 font-serif">
                            ৳ {{ number_format($calculation['grand_total']) }}
                        </span>
                    </div>
                </div>

                <!-- Coupon Form -->
                @if(! $calculation['coupon_code'])
                    <div class="pt-2">
                        <form wire:submit.prevent="applyCoupon" class="space-y-2">
                            <div class="flex items-center gap-2">
                                <input
                                    type="text"
                                    wire:model="couponInput"
                                    placeholder="Promo coupon code"
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
                    </div>
                @endif

                <!-- Desktop Place Order Button -->
                <div class="hidden lg:block pt-2">
                    <button
                        type="button"
                        wire:click="placeOrder"
                        wire:loading.attr="disabled"
                        :disabled="$wire.isSubmitting"
                        class="w-full py-4 rounded-2xl bg-rose-900 hover:bg-rose-800 text-white font-black text-sm uppercase tracking-wider shadow-xl hover:shadow-rose-900/30 transition-all flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span wire:loading.remove wire:target="placeOrder">
                            🛍️ Confirm Order with Cash on Delivery
                        </span>
                        <span wire:loading wire:target="placeOrder" class="flex items-center gap-2">
                            <span class="animate-spin inline-block">⏳</span> Processing Order...
                        </span>
                    </button>
                </div>

                <!-- Trust Guarantees -->
                <div class="pt-4 border-t border-stone-100 space-y-2 text-[11px] text-stone-500">
                    <div class="flex items-center gap-2">
                        <span>🛡️</span>
                        <span>7 Days Doorstep Size Exchange across Bangladesh.</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span>📦</span>
                        <span>Open package & inspect footwear before paying cash.</span>
                    </div>
                </div>

            </div>

        </div>

    </div>

</div>
