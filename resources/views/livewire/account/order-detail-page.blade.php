<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">

    <!-- Top Navigation / Breadcrumb -->
    <div class="mb-6 flex items-center justify-between">
        <a href="{{ route('account.dashboard', ['tab' => 'orders']) }}" class="text-xs font-bold text-rose-900 hover:underline flex items-center gap-1.5">
            <span>←</span>
            <span>Back to My Orders</span>
        </a>

        <div class="flex items-center gap-2">
            <button
                type="button"
                onclick="window.print()"
                class="px-3.5 py-1.5 rounded-xl border border-stone-200 bg-white hover:bg-stone-50 text-slate-700 text-xs font-semibold shadow-2xs flex items-center gap-1.5 cursor-pointer"
            >
                <span>🖨️</span>
                <span>Print Receipt</span>
            </button>
            <a
                href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $storeSettings['whatsapp_number']) }}?text={{ urlencode('Hi [BRAND_NAME], I am inquiring about Order #' . $order->order_number) }}"
                target="_blank"
                class="px-3.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold uppercase tracking-wider shadow-2xs flex items-center gap-1.5"
            >
                <span>💬</span>
                <span>WhatsApp Care</span>
            </a>
        </div>
    </div>

    @if($exchangeSuccess)
        <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-800 flex items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="text-xl">✓</span>
                <span>{{ $exchangeSuccess }}</span>
            </div>
            <button type="button" wire:click="$set('exchangeSuccess', null)" class="text-emerald-600 hover:text-emerald-900 font-bold">✕</button>
        </div>
    @endif

    <!-- MAIN ORDER CARD -->
    <div class="bg-white rounded-3xl border border-stone-200 p-6 sm:p-10 shadow-sm space-y-8">

        <!-- Order Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-stone-100">
            <div>
                <div class="flex items-center gap-2.5">
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 font-mono">
                        {{ $order->order_number }}
                    </h1>
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
                    Placed on {{ $order->created_at->format('d M Y, h:i A') }} • Recipient: <strong class="text-slate-800">{{ $order->customer_name }}</strong>
                </p>
            </div>

            <div class="text-left sm:text-right">
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
                            Courier Dispatch: {{ ucfirst($order->courier_name) }} Courier
                        </p>
                        <p class="text-[11px] text-stone-600 mt-0.5">
                            Tracking Code: <strong class="text-indigo-900 font-mono select-all">{{ $order->courier_tracking_code }}</strong>
                        </p>
                    </div>
                </div>
                <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-indigo-100 text-indigo-800">
                    {{ ucfirst(str_replace('_', ' ', $order->courier_status ?? 'In Transit')) }}
                </span>
            </div>
        @endif

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

        <!-- STATUS TIMELINE -->
        @if(! $isSpecialStatus)
            <div class="py-2">
                <div class="grid grid-cols-5 relative">
                    <div class="absolute top-5 inset-x-8 h-1 bg-stone-200 -z-0"></div>
                    <div
                        class="absolute top-5 left-8 h-1 bg-rose-800 transition-all duration-500 -z-0"
                        style="width: {{ max(0, min(100, (($currentStep - 1) / 4) * 100)) }}%;"
                    ></div>

                    <div class="flex flex-col items-center text-center relative z-10">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-xs {{ $currentStep >= 1 ? 'bg-rose-900 text-white ring-4 ring-rose-100' : 'bg-white border-2 border-stone-300 text-stone-400' }}">✓</div>
                        <span class="mt-2 text-[11px] font-bold {{ $currentStep >= 1 ? 'text-slate-900' : 'text-stone-400' }}">Placed</span>
                    </div>

                    <div class="flex flex-col items-center text-center relative z-10">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-xs {{ $currentStep >= 2 ? 'bg-rose-900 text-white ring-4 ring-rose-100' : 'bg-white border-2 border-stone-300 text-stone-400' }}">{{ $currentStep >= 2 ? '✓' : '2' }}</div>
                        <span class="mt-2 text-[11px] font-bold {{ $currentStep >= 2 ? 'text-slate-900' : 'text-stone-400' }}">Confirmed</span>
                    </div>

                    <div class="flex flex-col items-center text-center relative z-10">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-xs {{ $currentStep >= 3 ? 'bg-rose-900 text-white ring-4 ring-rose-100' : 'bg-white border-2 border-stone-300 text-stone-400' }}">{{ $currentStep >= 3 ? '✓' : '3' }}</div>
                        <span class="mt-2 text-[11px] font-bold {{ $currentStep >= 3 ? 'text-slate-900' : 'text-stone-400' }}">Packaging</span>
                    </div>

                    <div class="flex flex-col items-center text-center relative z-10">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-xs {{ $currentStep >= 4 ? 'bg-rose-900 text-white ring-4 ring-rose-100' : 'bg-white border-2 border-stone-300 text-stone-400' }}">{{ $currentStep >= 4 ? '✓' : '4' }}</div>
                        <span class="mt-2 text-[11px] font-bold {{ $currentStep >= 4 ? 'text-slate-900' : 'text-stone-400' }}">In Transit</span>
                    </div>

                    <div class="flex flex-col items-center text-center relative z-10">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-xs {{ $currentStep >= 5 ? 'bg-emerald-600 text-white ring-4 ring-emerald-100' : 'bg-white border-2 border-stone-300 text-stone-400' }}">{{ $currentStep >= 5 ? '✓' : '5' }}</div>
                        <span class="mt-2 text-[11px] font-bold {{ $currentStep >= 5 ? 'text-emerald-800' : 'text-stone-400' }}">Delivered</span>
                    </div>
                </div>
            </div>
        @else
            <div class="p-4 rounded-2xl {{ $order->status === 'cancelled' ? 'bg-rose-50 border border-rose-200 text-rose-900' : 'bg-purple-50 border border-purple-200 text-purple-900' }}">
                <p class="text-xs font-bold uppercase tracking-wider">Status: Order {{ ucfirst($order->status) }}</p>
            </div>
        @endif

        <!-- ITEMIZED FOOTWEAR LIST -->
        <div class="space-y-4 pt-4 border-t border-stone-100">
            <h3 class="text-xs font-bold uppercase tracking-wider text-stone-400">
                Ordered Footwear Items ({{ $order->items->sum('quantity') }} pairs)
            </h3>

            <div class="divide-y divide-stone-100 space-y-4">
                @foreach($order->items as $item)
                    <div class="pt-4 first:pt-0 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-14 h-14 rounded-xl overflow-hidden bg-stone-100 border border-stone-200 shrink-0">
                                <img
                                    src="{{ $item->variant?->product?->primaryImage?->image_url ?? asset('images/placeholder-shoe.webp') }}"
                                    alt="{{ $item->product_name }}"
                                    class="w-full h-full object-cover"
                                >
                            </div>
                            <div class="min-w-0">
                                <h4 class="text-sm font-bold text-slate-900 truncate">{{ $item->product_name }}</h4>
                                <p class="text-xs text-stone-500">
                                    {{ $item->variant_details['color'] ?? '' }} • EU Size {{ $item->variant_details['size'] ?? '' }}
                                    @if(! empty($item->variant_details['sku']))
                                        <span class="font-mono text-[10px] text-stone-400">({{ $item->variant_details['sku'] }})</span>
                                    @endif
                                </p>
                                <p class="text-xs text-stone-600 mt-0.5">
                                    ৳ {{ number_format($item->unit_price) }} × {{ $item->quantity }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center justify-between sm:justify-end gap-4">
                            <span class="text-sm font-black text-slate-900 font-serif">
                                ৳ {{ number_format($item->total_price) }}
                            </span>

                            @if(in_array($order->status, ['confirmed', 'delivered']))
                                <button
                                    type="button"
                                    wire:click="openExchangeModal({{ $item->id }})"
                                    class="px-3 py-1.5 rounded-xl bg-stone-100 hover:bg-stone-200 text-slate-800 text-xs font-bold transition flex items-center gap-1 shrink-0"
                                >
                                    <span>🔄</span>
                                    <span>Exchange Size</span>
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- BREAKDOWN & ADDRESS GRID -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-stone-100">

            <!-- Shipping Destination -->
            <div class="space-y-2 text-xs">
                <span class="font-bold text-stone-400 uppercase tracking-wider block">Delivery Destination</span>
                <p class="font-bold text-sm text-slate-900">{{ $order->customer_name }}</p>
                <p class="font-mono text-stone-600">📞 {{ $order->customer_phone }}</p>
                <p class="text-slate-800">{{ $order->shipping_address }}</p>
                <p class="text-stone-500">{{ $order->shipping_thana }}, {{ $order->shipping_district }} ({{ $order->shipping_division }} Division)</p>
                @if($order->notes)
                    <p class="pt-1 text-[11px] text-stone-400 italic">"{{ $order->notes }}"</p>
                @endif
            </div>

            <!-- Financials -->
            <div class="space-y-2 text-xs text-stone-600 bg-stone-50 p-4 rounded-2xl border border-stone-100">
                <div class="flex justify-between">
                    <span>Subtotal:</span>
                    <span class="font-semibold text-slate-900 font-serif">৳ {{ number_format($order->subtotal) }}</span>
                </div>
                @if($order->discount_amount > 0)
                    <div class="flex justify-between text-rose-800">
                        <span>Coupon Discount ({{ $order->coupon_code }}):</span>
                        <span class="font-semibold">- ৳ {{ number_format($order->discount_amount) }}</span>
                    </div>
                @endif
                <div class="flex justify-between">
                    <span>Delivery Charge:</span>
                    <span class="font-semibold text-slate-900 font-serif">
                        {{ $order->delivery_charge > 0 ? '৳ ' . number_format($order->delivery_charge) : 'FREE' }}
                    </span>
                </div>
                <div class="flex justify-between pt-2 border-t border-stone-200 text-base font-black text-rose-950 font-serif">
                    <span>Grand Total:</span>
                    <span>৳ {{ number_format($order->total) }}</span>
                </div>
            </div>

        </div>

    </div>

    <!-- EXCHANGE REQUEST MODAL -->
    @if($showExchangeModal)
        <div class="fixed inset-0 z-50 overflow-y-auto p-4 flex items-center justify-center bg-slate-950/70 backdrop-blur-xs">
            <div class="relative bg-white rounded-3xl border border-stone-200 p-6 sm:p-8 max-w-lg w-full shadow-2xl space-y-5 animate-scale-in">
                <div class="flex items-center justify-between pb-3 border-b border-stone-100">
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900 font-serif">
                            Request Size / Style Exchange
                        </h3>
                        <p class="text-[11px] text-stone-500">Free doorstep pickup and replacement within 7 days.</p>
                    </div>
                    <button type="button" wire:click="$set('showExchangeModal', false)" class="text-stone-400 hover:text-slate-800 text-lg">✕</button>
                </div>

                <form wire:submit.prevent="submitExchange" class="space-y-4">
                    <!-- Reason -->
                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Reason for Exchange *</label>
                        <select wire:model="exchange_reason" class="w-full px-3.5 py-2.5 rounded-xl border border-stone-200 text-xs text-slate-900 bg-white focus:outline-hidden focus:ring-2 focus:ring-rose-800">
                            <option value="Size too small - need larger size">Size too small (Need larger size)</option>
                            <option value="Size too large - need smaller size">Size too large (Need smaller size)</option>
                            <option value="Want to change color / model">Want to change color / model</option>
                            <option value="Shoe defect or finish issue">Shoe defect or finish issue</option>
                            <option value="Other / Comfort reason">Other / Comfort reason</option>
                        </select>
                    </div>

                    <!-- Customer Notes -->
                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                            Replacement Notes / Desired Size *
                        </label>
                        <textarea
                            wire:model="exchange_notes"
                            rows="2"
                            placeholder="e.g. Please swap with EU 38 instead of EU 37."
                            class="w-full px-3.5 py-2.5 rounded-xl border border-stone-200 text-xs text-slate-900 focus:outline-hidden focus:ring-2 focus:ring-rose-800"
                        ></textarea>
                        @error('exchange_notes') <p class="text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <!-- Photo Upload -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                            Footwear Photo (Optional)
                        </label>
                        <input
                            type="file"
                            wire:model="exchange_photo"
                            accept="image/*"
                            class="block w-full text-xs text-stone-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-rose-50 file:text-rose-900 hover:file:bg-rose-100"
                        >
                        @if ($exchange_photo)
                            <div class="mt-2 w-20 h-20 rounded-xl overflow-hidden border border-stone-200 bg-stone-100">
                                <img src="{{ $exchange_photo->temporaryUrl() }}" alt="Preview" class="w-full h-full object-cover">
                            </div>
                        @endif
                        @error('exchange_photo') <p class="text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-stone-100">
                        <button type="button" wire:click="$set('showExchangeModal', false)" class="px-4 py-2.5 rounded-xl bg-stone-100 text-slate-700 text-xs font-bold hover:bg-stone-200">
                            Cancel
                        </button>
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            class="px-5 py-2.5 rounded-xl bg-rose-900 hover:bg-rose-800 text-white text-xs font-bold uppercase tracking-wider shadow-md disabled:opacity-50"
                        >
                            <span wire:loading.remove wire:target="submitExchange">Submit Request</span>
                            <span wire:loading wire:target="submitExchange">Uploading...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

</div>
