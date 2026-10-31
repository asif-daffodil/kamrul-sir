<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-14">

    <!-- PAGE TITLE & HERO -->
    <div class="text-center space-y-3 mb-8">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-rose-100 text-rose-900 text-xs font-bold uppercase tracking-wider">
            <span>🚚</span> Live Delivery Tracking
        </span>
        <h1 class="text-3xl sm:text-4xl font-black text-slate-900 font-serif">
            Track Your Footwear Order
        </h1>
        <p class="text-sm text-stone-600 max-w-md mx-auto">
            Enter your unique Order ID and the 11-digit mobile number provided during checkout to track live delivery progress.
        </p>
    </div>

    <!-- TRACKING LOOKUP FORM CARD -->
    <div class="bg-white rounded-3xl border border-stone-200 p-6 sm:p-8 shadow-xl max-w-2xl mx-auto mb-10">
        <form wire:submit.prevent="track" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Order Number Input -->
                <div class="space-y-1.5">
                    <label for="orderNumber" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                        Order ID <span class="text-rose-600">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-stone-400 text-sm">
                            🏷️
                        </span>
                        <input
                            type="text"
                            id="orderNumber"
                            wire:model="orderNumber"
                            placeholder="ORD-20261022-XXXXX"
                            class="w-full pl-9 pr-3 py-3 rounded-xl border border-stone-200 text-xs sm:text-sm font-mono uppercase text-slate-900 focus:outline-hidden focus:ring-2 focus:ring-rose-800"
                        >
                    </div>
                    @error('orderNumber')
                        <p class="text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Phone Number Input -->
                <div class="space-y-1.5">
                    <label for="phone" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                        Mobile Phone <span class="text-rose-600">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-stone-400 text-xs font-bold">
                            +88
                        </span>
                        <input
                            type="tel"
                            id="phone"
                            wire:model="phone"
                            placeholder="01712345678"
                            class="w-full pl-11 pr-3 py-3 rounded-xl border border-stone-200 text-xs sm:text-sm font-mono text-slate-900 focus:outline-hidden focus:ring-2 focus:ring-rose-800"
                        >
                    </div>
                    @error('phone')
                        <p class="text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <button
                type="submit"
                wire:loading.attr="disabled"
                class="w-full py-3.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs sm:text-sm uppercase tracking-wider transition shadow-md flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50"
            >
                <span wire:loading.remove wire:target="track">🔍 Check Live Status</span>
                <span wire:loading wire:target="track" class="flex items-center gap-2">
                    <span class="animate-spin inline-block">⏳</span> Searching order records...
                </span>
            </button>
        </form>

        @if($errorMessage)
            <div class="mt-4 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-xs text-rose-800 flex items-start gap-2.5">
                <span class="text-base shrink-0">⚠️</span>
                <div>
                    <p class="font-bold">Order Not Found</p>
                    <p class="mt-0.5 text-rose-700">{{ $errorMessage }}</p>
                    <p class="mt-1 text-[11px] text-rose-600">Need immediate help? Call our hotline: <strong>{{ $storeSettings['support_phone'] }}</strong></p>
                </div>
            </div>
        @endif
    </div>

    <!-- TRACKING RESULT DETAILS -->
    @if($order)
        <div class="space-y-8 animate-fade-in">

            @php
                $statusHierarchy = [
                    'pending' => 1,
                    'confirmed' => 2,
                    'processing' => 3,
                    'shipped' => 4,
                    'delivered' => 5,
                ];
                $currentStep = $statusHierarchy[$order->status] ?? 0;
                $isSpecialStatus = in_array($order->status, ['cancelled', 'returned']);
            @endphp

            <!-- STATUS TIMELINE CARD -->
            <div class="bg-white rounded-3xl border border-stone-200 p-6 sm:p-10 shadow-xl space-y-8">

                <!-- Header Info -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-stone-100">
                    <div>
                        <div class="flex items-center gap-2.5">
                            <h2 class="text-xl sm:text-2xl font-black text-slate-900 font-mono">
                                {{ $order->order_number }}
                            </h2>
                            <span class="px-3 py-1 rounded-full text-xs font-extrabold uppercase tracking-wider {{ match($order->status) {
                                'delivered' => 'bg-emerald-100 text-emerald-800',
                                'shipped' => 'bg-blue-100 text-blue-800',
                                'processing' => 'bg-amber-100 text-amber-800',
                                'confirmed' => 'bg-indigo-100 text-indigo-800',
                                'cancelled' => 'bg-rose-100 text-rose-800',
                                'returned' => 'bg-purple-100 text-purple-800',
                                default => 'bg-stone-100 text-stone-800',
                            } }}">
                                {{ ucfirst($order->status) }}
                            </span>
                        </div>
                        <p class="text-xs text-stone-500 mt-1">
                            Placed on {{ $order->created_at->setTimezone('Asia/Dhaka')->format('d M Y, h:i A') }} • Recipient: <strong class="text-slate-800">{{ $order->customer_name }}</strong>
                        </p>
                    </div>

                    <div class="text-right">
                        <span class="text-xs text-stone-400 block font-normal">Amount to Pay</span>
                        <span class="text-2xl font-black text-rose-950 font-serif">৳ {{ number_format($order->total) }}</span>
                        <span class="text-[10px] text-stone-500 block uppercase font-mono font-bold">{{ strtoupper($order->payment_method) }} ({{ ucfirst($order->payment_status) }})</span>
                    </div>
                </div>

                @if($order->courier_tracking_code)
                    <!-- Active Courier Consignment Tracking Badge -->
                    <div class="p-4 rounded-2xl bg-indigo-50/70 border border-indigo-200/60 flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">🚚</span>
                            <div>
                                <p class="text-xs font-bold text-slate-900">
                                    Dispatched with {{ ucfirst($order->courier_name) }} Courier
                                </p>
                                <p class="text-[11px] text-stone-600 mt-0.5">
                                    Consignment / Tracking: <strong class="text-indigo-900 font-mono select-all">{{ $order->courier_tracking_code }}</strong>
                                </p>
                            </div>
                        </div>
                        <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-indigo-100 text-indigo-800">
                            {{ ucfirst(str_replace('_', ' ', $order->courier_status ?? 'In Transit')) }}
                        </span>
                    </div>
                @endif

                @if($isSpecialStatus)
                    <!-- Special Alert for Cancelled or Returned -->
                    <div class="p-5 rounded-2xl {{ $order->status === 'cancelled' ? 'bg-rose-50 border border-rose-200 text-rose-900' : 'bg-purple-50 border border-purple-200 text-purple-900' }}">
                        <div class="flex items-center gap-3">
                            <span class="text-3xl">{{ $order->status === 'cancelled' ? '🛑' : '🔄' }}</span>
                            <div>
                                <h4 class="text-sm font-bold uppercase tracking-wider">
                                    Order {{ ucfirst($order->status) }}
                                </h4>
                                <p class="text-xs mt-1">
                                    {{ $order->status === 'cancelled'
                                        ? 'This order was cancelled. If you believe this is in error, please reach out via WhatsApp or hotline.'
                                        : 'This order was returned to our fulfillment warehouse in Dhaka.' }}
                                </p>
                            </div>
                        </div>
                    </div>
                @else
                    <!-- 5-STEP HORIZONTAL STATUS TIMELINE -->
                    <div class="py-4">
                        <div class="grid grid-cols-5 relative">
                            <!-- Background connecting bar -->
                            <div class="absolute top-5 inset-x-8 h-1 bg-stone-200 -z-0"></div>
                            <!-- Active progress bar -->
                            <div
                                class="absolute top-5 left-8 h-1 bg-rose-800 transition-all duration-500 -z-0"
                                style="width: {{ max(0, min(100, (($currentStep - 1) / 4) * 100)) }}%;"
                            ></div>

                            <!-- Step 1: Placed -->
                            <div class="flex flex-col items-center text-center relative z-10">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-xs shadow-xs {{ $currentStep >= 1 ? 'bg-rose-900 text-white ring-4 ring-rose-100' : 'bg-white border-2 border-stone-300 text-stone-400' }}">
                                    ✓
                                </div>
                                <span class="mt-2 text-[11px] sm:text-xs font-bold {{ $currentStep >= 1 ? 'text-slate-900' : 'text-stone-400' }}">
                                    Placed
                                </span>
                                <span class="text-[9px] text-stone-400 hidden sm:block">Bag Ordered</span>
                            </div>

                            <!-- Step 2: Confirmed -->
                            <div class="flex flex-col items-center text-center relative z-10">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-xs shadow-xs {{ $currentStep >= 2 ? 'bg-rose-900 text-white ring-4 ring-rose-100' : 'bg-white border-2 border-stone-300 text-stone-400' }}">
                                    {{ $currentStep >= 2 ? '✓' : '2' }}
                                </div>
                                <span class="mt-2 text-[11px] sm:text-xs font-bold {{ $currentStep >= 2 ? 'text-slate-900' : 'text-stone-400' }}">
                                    Confirmed
                                </span>
                                <span class="text-[9px] text-stone-400 hidden sm:block">Care Team Call</span>
                            </div>

                            <!-- Step 3: Processing -->
                            <div class="flex flex-col items-center text-center relative z-10">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-xs shadow-xs {{ $currentStep >= 3 ? 'bg-rose-900 text-white ring-4 ring-rose-100' : 'bg-white border-2 border-stone-300 text-stone-400' }}">
                                    {{ $currentStep >= 3 ? '✓' : '3' }}
                                </div>
                                <span class="mt-2 text-[11px] sm:text-xs font-bold {{ $currentStep >= 3 ? 'text-slate-900' : 'text-stone-400' }}">
                                    Packaging
                                </span>
                                <span class="text-[9px] text-stone-400 hidden sm:block">QC & Boxed</span>
                            </div>

                            <!-- Step 4: Shipped -->
                            <div class="flex flex-col items-center text-center relative z-10">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-xs shadow-xs {{ $currentStep >= 4 ? 'bg-rose-900 text-white ring-4 ring-rose-100' : 'bg-white border-2 border-stone-300 text-stone-400' }}">
                                    {{ $currentStep >= 4 ? '✓' : '4' }}
                                </div>
                                <span class="mt-2 text-[11px] sm:text-xs font-bold {{ $currentStep >= 4 ? 'text-slate-900' : 'text-stone-400' }}">
                                    In Transit
                                </span>
                                <span class="text-[9px] text-stone-400 hidden sm:block">Courier Handover</span>
                            </div>

                            <!-- Step 5: Delivered -->
                            <div class="flex flex-col items-center text-center relative z-10">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-xs shadow-xs {{ $currentStep >= 5 ? 'bg-emerald-600 text-white ring-4 ring-emerald-100' : 'bg-white border-2 border-stone-300 text-stone-400' }}">
                                    {{ $currentStep >= 5 ? '✓' : '5' }}
                                </div>
                                <span class="mt-2 text-[11px] sm:text-xs font-bold {{ $currentStep >= 5 ? 'text-emerald-800' : 'text-stone-400' }}">
                                    Delivered
                                </span>
                                <span class="text-[9px] text-stone-400 hidden sm:block">Doorstep</span>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Status Histories Timeline Log -->
                @if($order->statusHistories->isNotEmpty())
                    <div class="pt-4 border-t border-stone-100 space-y-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-stone-400">Activity History Log</span>
                        <div class="space-y-2.5">
                            @foreach($order->statusHistories->sortByDesc('created_at') as $history)
                                <div class="flex items-start gap-3 text-xs">
                                    <div class="w-2 h-2 rounded-full bg-rose-800 mt-1.5 shrink-0"></div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-semibold text-slate-800">
                                            Status set to <span class="font-bold text-rose-950 uppercase">{{ $history->to_status }}</span>
                                        </p>
                                        @if($history->comment)
                                            <p class="text-stone-500 text-[11px] mt-0.5">{{ $history->comment }}</p>
                                        @endif
                                    </div>
                                    <span class="text-[10px] text-stone-400 font-mono shrink-0">
                                        {{ $history->created_at->setTimezone('Asia/Dhaka')->format('d M, h:i A') }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

            </div>

            <!-- SHIPPING & ORDER DETAILS GRID -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- Shipping Destination Card -->
                <div class="bg-white rounded-3xl border border-stone-200 p-6 shadow-sm space-y-3">
                    <div class="flex items-center gap-2 text-rose-900 font-bold text-xs uppercase tracking-wider">
                        <span>📍</span>
                        <span>Delivery Destination</span>
                    </div>
                    <div class="text-xs space-y-1 text-slate-700">
                        <p class="font-bold text-sm text-slate-900">{{ $order->customer_name }}</p>
                        <p class="font-mono text-stone-600">📞 {{ $order->customer_phone }}</p>
                        @if($order->customer_email)
                            <p class="text-stone-500">✉️ {{ $order->customer_email }}</p>
                        @endif
                        <p class="pt-2 text-slate-800 font-medium">{{ $order->shipping_address }}</p>
                        <p class="text-stone-500">{{ $order->shipping_thana }}, {{ $order->shipping_district }}</p>
                        <p class="text-[11px] text-stone-400">{{ $order->shipping_division }} Division, Bangladesh</p>
                    </div>
                    @if($order->notes)
                        <div class="p-3 rounded-xl bg-stone-50 text-[11px] text-stone-600 border border-stone-100">
                            <strong>Note:</strong> {{ $order->notes }}
                        </div>
                    @endif
                </div>

                <!-- Footwear Package Summary Card -->
                <div class="bg-white rounded-3xl border border-stone-200 p-6 shadow-sm space-y-3">
                    <div class="flex items-center justify-between text-rose-900 font-bold text-xs uppercase tracking-wider">
                        <div class="flex items-center gap-2">
                            <span>👠</span>
                            <span>Ordered Pairs ({{ $order->items->sum('quantity') }})</span>
                        </div>
                        <span class="text-stone-400 normal-case font-normal text-[11px]">BDT (৳)</span>
                    </div>

                    <div class="divide-y divide-stone-100 max-h-56 overflow-y-auto pr-1 space-y-2">
                        @foreach($order->items as $item)
                            <div class="pt-2 first:pt-0 flex items-center justify-between gap-3 text-xs">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-10 h-10 rounded-lg overflow-hidden bg-stone-100 border border-stone-200 shrink-0">
                                        <img
                                            src="{{ $item->variant?->product?->primaryImage?->image_url ?? asset('images/placeholder-shoe.webp') }}"
                                            alt="{{ $item->product_name }}"
                                            class="w-full h-full object-cover"
                                        >
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-bold text-slate-900 truncate">{{ $item->product_name }}</p>
                                        <p class="text-[10px] text-stone-500">
                                            {{ $item->variant_details['color'] ?? '' }} • EU {{ $item->variant_details['size'] ?? '' }} × {{ $item->quantity }}
                                        </p>
                                    </div>
                                </div>
                                <span class="font-bold text-slate-900 font-serif shrink-0">
                                    ৳ {{ number_format($item->total_price) }}
                                </span>
                            </div>
                        @endforeach
                    </div>

                    <!-- Breakdown -->
                    <div class="pt-3 border-t border-stone-100 space-y-1 text-xs text-stone-600">
                        <div class="flex justify-between">
                            <span>Subtotal:</span>
                            <span class="font-semibold text-slate-900">৳ {{ number_format($order->subtotal) }}</span>
                        </div>
                        @if($order->discount_amount > 0)
                            <div class="flex justify-between text-rose-800">
                                <span>Coupon ({{ $order->coupon_code }}):</span>
                                <span>- ৳ {{ number_format($order->discount_amount) }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between">
                            <span>Delivery:</span>
                            <span class="font-semibold text-slate-900">
                                {{ $order->delivery_charge > 0 ? '৳ ' . number_format($order->delivery_charge) : 'FREE' }}
                            </span>
                        </div>
                        <div class="flex justify-between pt-1 border-t border-stone-200 text-sm font-black text-rose-950 font-serif">
                            <span>Total:</span>
                            <span>৳ {{ number_format($order->total) }}</span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- CUSTOMER ACTIONS & EXCHANGE PROMPT -->
            <div class="p-6 rounded-3xl bg-rose-50/60 border border-rose-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="space-y-1 text-center sm:text-left">
                    <h4 class="text-sm font-bold text-slate-900">Need a Different Size or Style?</h4>
                    <p class="text-xs text-stone-600">
                        We offer a 7-day hassle-free doorstep size exchange. Log in to your customer account to submit an exchange request.
                    </p>
                </div>
                <div class="flex items-center gap-3 shrink-0">
                    <a
                        href="{{ route('login') }}"
                        class="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs uppercase tracking-wider transition"
                    >
                        Customer Login
                    </a>
                    <a
                        href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $storeSettings['whatsapp_number']) }}?text={{ urlencode('Hi Heel Bari, I am inquiring about Order #' . $order->order_number) }}"
                        target="_blank"
                        class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs uppercase tracking-wider flex items-center gap-1.5 transition"
                    >
                        <span>💬</span> WhatsApp Care
                    </a>
                </div>
            </div>

        </div>
    @elseif($searched && ! $errorMessage)
        <div class="text-center py-12 text-stone-400">
            <span class="text-4xl block mb-2">📦</span>
            <p class="text-sm">Please provide both your Order ID and Phone Number above to view tracking.</p>
        </div>
    @endif

</div>
