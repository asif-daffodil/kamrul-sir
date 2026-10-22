<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">

    <!-- WELCOME & MEMBER PROFILE HEADER -->
    <div class="bg-white rounded-3xl border border-stone-200 p-6 sm:p-8 shadow-sm mb-8 relative overflow-hidden">
        <div class="absolute -top-10 -right-10 w-44 h-44 bg-rose-50 rounded-full blur-2xl pointer-events-none"></div>

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6 relative z-10">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-gradient-to-tr from-rose-950 to-rose-800 text-white font-serif font-black text-2xl sm:text-3xl flex items-center justify-center shadow-lg">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <h1 class="text-xl sm:text-2xl font-black text-slate-900 font-serif">
                            {{ $user->name }}
                        </h1>
                        <span class="px-2.5 py-0.5 rounded-full bg-rose-100 text-rose-900 text-[10px] font-extrabold uppercase tracking-wider">
                            Verified Member
                        </span>
                    </div>
                    <p class="text-xs text-stone-500 font-mono">
                        📞 {{ $user->phone ?? 'No phone added' }}
                        @if($user->email)
                            • ✉️ {{ $user->email }}
                        @endif
                    </p>
                    <p class="text-[11px] text-stone-400">
                        Member since {{ $user->created_at->format('M Y') }} • 7-Day Doorstep Exchange Eligible
                    </p>
                </div>
            </div>

            <!-- Header Quick Stats -->
            <div class="grid grid-cols-3 gap-3 border-t sm:border-t-0 sm:border-l border-stone-100 pt-4 sm:pt-0 sm:pl-6 text-center">
                <div class="px-2">
                    <span class="block text-2xl font-black text-slate-900 font-serif">{{ $orders->count() }}</span>
                    <span class="text-[10px] uppercase font-bold text-stone-400 tracking-wider">Orders</span>
                </div>
                <div class="px-2 border-x border-stone-100">
                    <span class="block text-2xl font-black text-slate-900 font-serif">{{ $addresses->count() }}</span>
                    <span class="text-[10px] uppercase font-bold text-stone-400 tracking-wider">Addresses</span>
                </div>
                <div class="px-2">
                    <span class="block text-2xl font-black text-rose-950 font-serif">{{ $exchangeRequests->count() }}</span>
                    <span class="text-[10px] uppercase font-bold text-stone-400 tracking-wider">Exchanges</span>
                </div>
            </div>
        </div>
    </div>

    @if($exchangeSuccessMessage)
        <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-800 flex items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="text-xl">✓</span>
                <span>{{ $exchangeSuccessMessage }}</span>
            </div>
            <button type="button" wire:click="$set('exchangeSuccessMessage', null)" class="text-emerald-600 hover:text-emerald-900 font-bold">✕</button>
        </div>
    @endif

    <!-- TAB NAVIGATION BAR -->
    <div class="flex items-center gap-2 border-b border-stone-200 pb-3 mb-8 overflow-x-auto no-scrollbar text-xs font-bold uppercase tracking-wider">
        <button
            type="button"
            wire:click="setTab('overview')"
            class="px-4 py-2.5 rounded-xl transition flex items-center gap-1.5 shrink-0 {{ $activeTab === 'overview' ? 'bg-rose-900 text-white shadow-sm' : 'text-slate-600 hover:bg-stone-100' }}"
        >
            <span>📊</span>
            <span>Dashboard Overview</span>
        </button>

        <button
            type="button"
            wire:click="setTab('orders')"
            class="px-4 py-2.5 rounded-xl transition flex items-center gap-1.5 shrink-0 {{ $activeTab === 'orders' ? 'bg-rose-900 text-white shadow-sm' : 'text-slate-600 hover:bg-stone-100' }}"
        >
            <span>🛍️</span>
            <span>My Orders ({{ $orders->count() }})</span>
        </button>

        <button
            type="button"
            wire:click="setTab('addresses')"
            class="px-4 py-2.5 rounded-xl transition flex items-center gap-1.5 shrink-0 {{ $activeTab === 'addresses' ? 'bg-rose-900 text-white shadow-sm' : 'text-slate-600 hover:bg-stone-100' }}"
        >
            <span>📍</span>
            <span>Saved Addresses ({{ $addresses->count() }})</span>
        </button>

        <button
            type="button"
            wire:click="setTab('exchanges')"
            class="px-4 py-2.5 rounded-xl transition flex items-center gap-1.5 shrink-0 {{ $activeTab === 'exchanges' ? 'bg-rose-900 text-white shadow-sm' : 'text-slate-600 hover:bg-stone-100' }}"
        >
            <span>🔄</span>
            <span>Exchange Requests ({{ $exchangeRequests->count() }})</span>
        </button>
    </div>

    <!-- TAB CONTENT: OVERVIEW -->
    @if($activeTab === 'overview')
        <div class="space-y-8 animate-fade-in">

            <!-- Recent Orders Snippet -->
            <div class="bg-white rounded-3xl border border-stone-200 p-6 sm:p-8 shadow-sm space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-stone-100">
                    <h3 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider font-serif">
                        Recent Footwear Orders
                    </h3>
                    @if($orders->isNotEmpty())
                        <button type="button" wire:click="setTab('orders')" class="text-xs font-bold text-rose-900 hover:underline">
                            View All ({{ $orders->count() }}) →
                        </button>
                    @endif
                </div>

                @if($orders->isEmpty())
                    <div class="text-center py-12 text-stone-400 space-y-3">
                        <span class="text-4xl block">🛍️</span>
                        <p class="text-sm font-medium">You haven't placed any orders yet.</p>
                        <a href="{{ route('products.index') }}" class="inline-block px-5 py-2.5 rounded-xl bg-rose-900 text-white text-xs font-bold uppercase tracking-wider hover:bg-rose-800 transition">
                            Explore Footwear Catalog
                        </a>
                    </div>
                @else
                    <div class="divide-y divide-stone-100 space-y-4">
                        @foreach($orders->take(3) as $order)
                            <div class="pt-4 first:pt-0 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('account.order.show', $order->order_number) }}" class="font-mono font-bold text-sm text-slate-900 hover:text-rose-900 underline">
                                            {{ $order->order_number }}
                                        </a>
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase {{ match($order->status) {
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
                                    <p class="text-xs text-stone-500">
                                        {{ $order->items->sum('quantity') }} pair(s) • ৳ {{ number_format($order->total) }} • Placed {{ $order->created_at->format('d M Y') }}
                                    </p>
                                </div>

                                <div class="flex items-center gap-3">
                                    <a
                                        href="{{ route('account.order.show', $order->order_number) }}"
                                        class="px-4 py-2 rounded-xl bg-stone-100 hover:bg-stone-200 text-slate-800 text-xs font-bold transition"
                                    >
                                        Order Details
                                    </a>
                                    @if(in_array($order->status, ['confirmed', 'delivered']))
                                        <button
                                            type="button"
                                            wire:click="openExchangeModal({{ $order->id }})"
                                            class="px-3.5 py-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-900 text-xs font-bold transition"
                                        >
                                            Exchange Size
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Quick Action Cards Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white rounded-3xl border border-stone-200 p-5 shadow-xs space-y-2">
                    <span class="text-2xl block">📍</span>
                    <h4 class="text-sm font-bold text-slate-900">Manage Delivery Addresses</h4>
                    <p class="text-xs text-stone-500">Save your home, office, and preferred delivery locations for 1-click checkout.</p>
                    <button type="button" wire:click="setTab('addresses')" class="text-xs font-bold text-rose-900 hover:underline pt-1 block">
                        Open Address Book →
                    </button>
                </div>

                <div class="bg-white rounded-3xl border border-stone-200 p-5 shadow-xs space-y-2">
                    <span class="text-2xl block">🔄</span>
                    <h4 class="text-sm font-bold text-slate-900">7-Day Size Exchange</h4>
                    <p class="text-xs text-stone-500">Ordered shoes that don't fit? We will swap for the correct size right at your door.</p>
                    <button type="button" wire:click="setTab('exchanges')" class="text-xs font-bold text-rose-900 hover:underline pt-1 block">
                        View Exchange Status →
                    </button>
                </div>

                <div class="bg-white rounded-3xl border border-stone-200 p-5 shadow-xs space-y-2">
                    <span class="text-2xl block">🚚</span>
                    <h4 class="text-sm font-bold text-slate-900">Live Courier Tracker</h4>
                    <p class="text-xs text-stone-500">Track shipments directly by order number and mobile phone.</p>
                    <a href="{{ route('track-order') }}" class="text-xs font-bold text-rose-900 hover:underline pt-1 block">
                        Track An Order →
                    </a>
                </div>
            </div>

        </div>
    @endif

    <!-- TAB CONTENT: MY ORDERS -->
    @if($activeTab === 'orders')
        <div class="space-y-6 animate-fade-in">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-black text-slate-900 font-serif">Order History</h2>
                <span class="text-xs text-stone-500 font-semibold">{{ $orders->count() }} Total Orders</span>
            </div>

            @if($orders->isEmpty())
                <div class="bg-white rounded-3xl border border-stone-200 p-12 text-center text-stone-400 space-y-3">
                    <span class="text-5xl block">📦</span>
                    <p class="text-sm font-semibold text-slate-800">No orders recorded yet.</p>
                    <p class="text-xs text-stone-500">When you place an order, it will appear here with live tracking updates.</p>
                    <a href="{{ route('products.index') }}" class="inline-block mt-2 px-6 py-2.5 rounded-xl bg-rose-900 text-white text-xs font-bold uppercase tracking-wider hover:bg-rose-800 transition">
                        Shop Best Sellers
                    </a>
                </div>
            @else
                <div class="space-y-4">
                    @foreach($orders as $order)
                        <div class="bg-white rounded-3xl border border-stone-200 p-5 sm:p-6 shadow-xs space-y-4 hover:border-stone-300 transition">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-stone-100">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('account.order.show', $order->order_number) }}" class="font-mono font-black text-slate-900 text-base hover:text-rose-900 underline">
                                            {{ $order->order_number }}
                                        </a>
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase {{ match($order->status) {
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
                                    <p class="text-xs text-stone-500 mt-0.5">
                                        Placed on {{ $order->created_at->format('d M Y, h:i A') }} • Shipping to: <strong class="text-slate-800">{{ $order->shipping_district }}</strong>
                                    </p>
                                </div>

                                <div class="text-left sm:text-right">
                                    <span class="text-base font-black text-rose-950 font-serif">৳ {{ number_format($order->total) }}</span>
                                    <span class="text-[10px] text-stone-400 block uppercase font-mono">{{ strtoupper($order->payment_method) }}</span>
                                </div>
                            </div>

                            <!-- Items Mini-List -->
                            <div class="space-y-2">
                                @foreach($order->items as $item)
                                    <div class="flex items-center justify-between text-xs">
                                        <div class="flex items-center gap-2.5 min-w-0">
                                            <div class="w-9 h-9 rounded-lg bg-stone-100 border border-stone-200 overflow-hidden shrink-0">
                                                <img
                                                    src="{{ $item->variant?->product?->primaryImage?->image_url ?? asset('images/placeholder-shoe.webp') }}"
                                                    alt="{{ $item->product_name }}"
                                                    class="w-full h-full object-cover"
                                                >
                                            </div>
                                            <div class="min-w-0">
                                                <p class="font-bold text-slate-800 truncate">{{ $item->product_name }}</p>
                                                <p class="text-[10px] text-stone-500">{{ $item->variant_details['color'] ?? '' }} • EU {{ $item->variant_details['size'] ?? '' }} × {{ $item->quantity }}</p>
                                            </div>
                                        </div>
                                        <span class="font-semibold text-slate-900 font-serif">৳ {{ number_format($item->total_price) }}</span>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Action Bar -->
                            <div class="pt-3 border-t border-stone-100 flex items-center justify-between">
                                <a
                                    href="{{ route('account.order.show', $order->order_number) }}"
                                    class="text-xs font-bold text-rose-900 hover:underline flex items-center gap-1"
                                >
                                    <span>View Detailed Receipt & Timeline</span>
                                    <span>→</span>
                                </a>

                                @if(in_array($order->status, ['confirmed', 'delivered']))
                                    <button
                                        type="button"
                                        wire:click="openExchangeModal({{ $order->id }})"
                                        class="px-3 py-1.5 rounded-lg bg-stone-100 hover:bg-stone-200 text-slate-800 text-xs font-bold transition flex items-center gap-1"
                                    >
                                        <span>🔄</span> Request Size Exchange
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    <!-- TAB CONTENT: SAVED ADDRESSES -->
    @if($activeTab === 'addresses')
        <div class="space-y-6 animate-fade-in">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-black text-slate-900 font-serif">Saved Addresses</h2>
                    <p class="text-xs text-stone-500">Addresses saved here are automatically pre-filled at checkout.</p>
                </div>
                <button
                    type="button"
                    wire:click="openNewAddressModal"
                    class="px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs uppercase tracking-wider transition flex items-center gap-1.5"
                >
                    <span>+ Add New Address</span>
                </button>
            </div>

            @if($addresses->isEmpty())
                <div class="bg-white rounded-3xl border border-stone-200 p-12 text-center text-stone-400 space-y-3">
                    <span class="text-4xl block">📍</span>
                    <p class="text-sm font-semibold text-slate-800">No saved addresses yet.</p>
                    <p class="text-xs text-stone-500">Save your primary delivery address for faster checkout.</p>
                    <button
                        type="button"
                        wire:click="openNewAddressModal"
                        class="px-5 py-2.5 rounded-xl bg-rose-900 text-white text-xs font-bold uppercase tracking-wider hover:bg-rose-800 transition"
                    >
                        + Add First Address
                    </button>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach($addresses as $addr)
                        <div class="bg-white rounded-3xl border {{ $addr->is_default ? 'border-rose-800 ring-2 ring-rose-100' : 'border-stone-200' }} p-5 shadow-xs space-y-3 relative">
                            @if($addr->is_default)
                                <span class="absolute top-4 right-4 px-2.5 py-0.5 rounded-full bg-rose-100 text-rose-900 text-[10px] font-extrabold uppercase tracking-wider">
                                    Default Delivery
                                </span>
                            @endif

                            <div class="space-y-1 text-xs">
                                <h4 class="font-bold text-sm text-slate-900">{{ $addr->name }}</h4>
                                <p class="font-mono text-stone-600">📞 {{ $addr->phone }}</p>
                                <p class="pt-1 text-slate-800 font-medium">{{ $addr->address_line }}</p>
                                <p class="text-stone-500">{{ $addr->thana }}, {{ $addr->district }}</p>
                                <p class="text-[11px] text-stone-400">{{ $addr->division }} Division, Bangladesh</p>
                            </div>

                            <div class="pt-3 border-t border-stone-100 flex items-center justify-between text-xs font-bold">
                                <div class="flex items-center gap-3">
                                    <button
                                        type="button"
                                        wire:click="openEditAddressModal({{ $addr->id }})"
                                        class="text-slate-700 hover:text-rose-900"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        type="button"
                                        wire:click="deleteAddress({{ $addr->id }})"
                                        wire:confirm="Are you sure you want to delete this address?"
                                        class="text-rose-600 hover:text-rose-900"
                                    >
                                        Delete
                                    </button>
                                </div>

                                @if(! $addr->is_default)
                                    <button
                                        type="button"
                                        wire:click="setDefaultAddress({{ $addr->id }})"
                                        class="text-rose-900 hover:underline"
                                    >
                                        Set as Default
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    <!-- TAB CONTENT: EXCHANGE REQUESTS -->
    @if($activeTab === 'exchanges')
        <div class="space-y-6 animate-fade-in">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-black text-slate-900 font-serif">Size Exchange Requests</h2>
                    <p class="text-xs text-stone-500">Track resolution of footwear size and style swap requests.</p>
                </div>
                @if($orders->whereIn('status', ['confirmed', 'delivered'])->isNotEmpty())
                    <button
                        type="button"
                        wire:click="openExchangeModal()"
                        class="px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs uppercase tracking-wider transition flex items-center gap-1.5"
                    >
                        <span>+ New Request</span>
                    </button>
                @endif
            </div>

            @if($exchangeRequests->isEmpty())
                <div class="bg-white rounded-3xl border border-stone-200 p-12 text-center text-stone-400 space-y-3">
                    <span class="text-4xl block">🔄</span>
                    <p class="text-sm font-semibold text-slate-800">No exchange requests filed.</p>
                    <p class="text-xs text-stone-500">Need a different size for shoes you received? Initiate a swap anytime within 7 days.</p>
                </div>
            @else
                <div class="space-y-4">
                    @foreach($exchangeRequests as $request)
                        <div class="bg-white rounded-3xl border border-stone-200 p-5 sm:p-6 shadow-xs space-y-4">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-stone-100">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono font-bold text-xs text-stone-400">Request #{{ $request->id }}</span>
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase {{ match($request->status) {
                                            'completed' => 'bg-emerald-100 text-emerald-800',
                                            'approved' => 'bg-blue-100 text-blue-800',
                                            'rejected' => 'bg-rose-100 text-rose-800',
                                            default => 'bg-amber-100 text-amber-800',
                                        } }}">
                                            {{ ucfirst($request->status) }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-stone-500 mt-0.5">
                                        For Order <a href="{{ route('account.order.show', $request->order->order_number) }}" class="font-mono font-bold text-slate-900 underline">{{ $request->order->order_number }}</a>
                                        • Submitted on {{ $request->created_at->format('d M Y, h:i A') }}
                                    </p>
                                </div>

                                <span class="text-xs font-semibold text-slate-700 bg-stone-100 px-3 py-1.5 rounded-xl self-start sm:self-auto">
                                    Reason: {{ $request->reason }}
                                </span>
                            </div>

                            <!-- Item & Photo Details -->
                            <div class="flex items-start gap-4">
                                @if($request->photo_path)
                                    <div class="w-16 h-16 rounded-xl overflow-hidden border border-stone-200 bg-stone-100 shrink-0">
                                        <a href="{{ $request->photo_url }}" target="_blank">
                                            <img src="{{ $request->photo_url }}" alt="Inspection Photo" class="w-full h-full object-cover">
                                        </a>
                                    </div>
                                @endif

                                <div class="space-y-1 text-xs text-slate-700 flex-1 min-w-0">
                                    <p class="font-bold text-slate-900">
                                        Item: {{ $request->orderItem->product_name }}
                                        ({{ $request->orderItem->variant_details['color'] ?? '' }} / EU {{ $request->orderItem->variant_details['size'] ?? '' }})
                                    </p>
                                    @if($request->customer_notes)
                                        <p class="text-stone-600 italic">"{{ $request->customer_notes }}"</p>
                                    @endif
                                    @if($request->admin_notes)
                                        <div class="p-3 rounded-xl bg-stone-50 border border-stone-200 text-[11px] text-slate-800 mt-2">
                                            <strong class="text-rose-950 font-bold block mb-0.5">Care Team Update:</strong>
                                            {{ $request->admin_notes }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    <!-- ───────────────────────────────────────────────────────────── -->
    <!-- ADDRESS MODAL -->
    <!-- ───────────────────────────────────────────────────────────── -->
    @if($showAddressModal)
        <div class="fixed inset-0 z-50 overflow-y-auto p-4 flex items-center justify-center bg-slate-950/70 backdrop-blur-xs">
            <div class="relative bg-white rounded-3xl border border-stone-200 p-6 sm:p-8 max-w-lg w-full shadow-2xl space-y-5 animate-scale-in">
                <div class="flex items-center justify-between pb-3 border-b border-stone-100">
                    <h3 class="text-base font-extrabold text-slate-900 font-serif">
                        {{ $editingAddressId ? 'Edit Delivery Address' : 'Add New Delivery Address' }}
                    </h3>
                    <button type="button" wire:click="$set('showAddressModal', false)" class="text-stone-400 hover:text-slate-800 text-lg">✕</button>
                </div>

                <form wire:submit.prevent="saveAddress" class="space-y-3.5">
                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Recipient Name *</label>
                        <input type="text" wire:model="address_name" class="w-full px-3.5 py-2.5 rounded-xl border border-stone-200 text-xs text-slate-900 focus:outline-hidden focus:ring-2 focus:ring-rose-800">
                        @error('address_name') <p class="text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Mobile Phone (BD) *</label>
                        <input type="tel" wire:model="address_phone" placeholder="01712345678" class="w-full px-3.5 py-2.5 rounded-xl border border-stone-200 text-xs font-mono text-slate-900 focus:outline-hidden focus:ring-2 focus:ring-rose-800">
                        @error('address_phone') <p class="text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Division *</label>
                            <select wire:model.live="address_division" class="w-full px-3 py-2.5 rounded-xl border border-stone-200 text-xs text-slate-900 bg-white focus:outline-hidden focus:ring-2 focus:ring-rose-800">
                                @foreach($divisions as $div)
                                    <option value="{{ $div }}">{{ $div }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-1">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">District *</label>
                            <select wire:model="address_district" class="w-full px-3 py-2.5 rounded-xl border border-stone-200 text-xs text-slate-900 bg-white focus:outline-hidden focus:ring-2 focus:ring-rose-800">
                                @foreach($districts as $dist)
                                    <option value="{{ $dist }}">{{ $dist }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Thana / Area *</label>
                        <input type="text" wire:model="address_thana" placeholder="e.g. Dhanmondi, Uttara, GEC" class="w-full px-3.5 py-2.5 rounded-xl border border-stone-200 text-xs text-slate-900 focus:outline-hidden focus:ring-2 focus:ring-rose-800">
                        @error('address_thana') <p class="text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Street Address / House & Road *</label>
                        <textarea wire:model="address_line" rows="2" placeholder="House 42, Road 27, Flat 3B" class="w-full px-3.5 py-2.5 rounded-xl border border-stone-200 text-xs text-slate-900 focus:outline-hidden focus:ring-2 focus:ring-rose-800"></textarea>
                        @error('address_line') <p class="text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <div class="pt-1">
                        <label class="flex items-center gap-2 cursor-pointer text-xs text-slate-700">
                            <input type="checkbox" wire:model="address_is_default" class="w-4 h-4 rounded text-rose-800 focus:ring-rose-800 border-stone-300">
                            <span>Set as default shipping address</span>
                        </label>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-stone-100">
                        <button type="button" wire:click="$set('showAddressModal', false)" class="px-4 py-2.5 rounded-xl bg-stone-100 text-slate-700 text-xs font-bold hover:bg-stone-200">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-rose-900 hover:bg-rose-800 text-white text-xs font-bold uppercase tracking-wider shadow-md">
                            Save Address
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- ───────────────────────────────────────────────────────────── -->
    <!-- EXCHANGE REQUEST MODAL -->
    <!-- ───────────────────────────────────────────────────────────── -->
    @if($showExchangeModal)
        <div class="fixed inset-0 z-50 overflow-y-auto p-4 flex items-center justify-center bg-slate-950/70 backdrop-blur-xs">
            <div class="relative bg-white rounded-3xl border border-stone-200 p-6 sm:p-8 max-w-lg w-full shadow-2xl space-y-5 animate-scale-in">
                <div class="flex items-center justify-between pb-3 border-b border-stone-100">
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900 font-serif">
                            Submit 7-Day Size Exchange Request
                        </h3>
                        <p class="text-[11px] text-stone-500">Free doorstep pickup & replacement across Bangladesh.</p>
                    </div>
                    <button type="button" wire:click="$set('showExchangeModal', false)" class="text-stone-400 hover:text-slate-800 text-lg">✕</button>
                </div>

                <form wire:submit.prevent="submitExchangeRequest" class="space-y-4">
                    <!-- Order Selection (if not pre-selected) -->
                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Select Footwear Order *</label>
                        <select wire:model.live="selectedOrderId" class="w-full px-3.5 py-2.5 rounded-xl border border-stone-200 text-xs text-slate-900 bg-white focus:outline-hidden focus:ring-2 focus:ring-rose-800">
                            <option value="">-- Choose Order --</option>
                            @foreach($orders as $o)
                                <option value="{{ $o->id }}">{{ $o->order_number }} (৳ {{ number_format($o->total) }} - {{ ucfirst($o->status) }})</option>
                            @endforeach
                        </select>
                        @error('selectedOrderId') <p class="text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <!-- Order Item Selection -->
                    @if($selectedOrderId)
                        @php
                            $currentOrder = $orders->firstWhere('id', $selectedOrderId);
                        @endphp
                        <div class="space-y-1">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Select Shoe Pair to Exchange *</label>
                            <select wire:model="selectedOrderItemId" class="w-full px-3.5 py-2.5 rounded-xl border border-stone-200 text-xs text-slate-900 bg-white focus:outline-hidden focus:ring-2 focus:ring-rose-800">
                                <option value="">-- Choose Pair --</option>
                                @foreach($currentOrder->items ?? [] as $item)
                                    <option value="{{ $item->id }}">
                                        {{ $item->product_name }} ({{ $item->variant_details['color'] ?? '' }} / EU {{ $item->variant_details['size'] ?? '' }}) - ৳ {{ number_format($item->total_price) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('selectedOrderItemId') <p class="text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror
                        </div>
                    @endif

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
                            Replacement Notes / Desired Size
                        </label>
                        <textarea
                            wire:model="exchange_notes"
                            rows="2"
                            placeholder="e.g. Please replace with EU 38 instead of EU 37. Midnight Black color is fine."
                            class="w-full px-3.5 py-2.5 rounded-xl border border-stone-200 text-xs text-slate-900 focus:outline-hidden focus:ring-2 focus:ring-rose-800"
                        ></textarea>
                        @error('exchange_notes') <p class="text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <!-- Photo Upload -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                            Upload Footwear Photo (Optional)
                        </label>
                        <input
                            type="file"
                            wire:model="exchange_photo"
                            accept="image/*"
                            class="block w-full text-xs text-stone-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-rose-50 file:text-rose-900 hover:file:bg-rose-100"
                        >
                        <p class="text-[10px] text-stone-400">Attach photo of shoe condition or sizing issue (Max 5MB).</p>

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
                            <span wire:loading.remove wire:target="submitExchangeRequest">Submit Request</span>
                            <span wire:loading wire:target="submitExchangeRequest">Uploading...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

</div>
