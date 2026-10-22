<!DOCTYPE html>
<html lang="en" class="h-full bg-stone-50 text-slate-800 antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', ($storeSettings['shop_name'] ?? '[BRAND_NAME]') . ' | Premium Handcrafted Women\'s Footwear Bangladesh')</title>
    <meta name="description" content="@yield('meta_description', 'Discover handcrafted women\'s footwear in Bangladesh: elegant block heels, cushioned daily flats, braided slides, modern mules, Boston clogs & festive Kolhapuri chappals. Cash on Delivery across all 64 districts.')">
    <meta name="robots" content="@yield('meta_robots', 'index, follow, max-image-preview:large')">
    <link rel="canonical" href="@yield('canonical_url', url()->current())">

    <!-- OpenGraph / Facebook Meta -->
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:site_name" content="{{ $storeSettings['shop_name'] ?? '[BRAND_NAME]' }}">
    <meta property="og:url" content="@yield('canonical_url', url()->current())">
    <meta property="og:title" content="@yield('og_title', ($storeSettings['shop_name'] ?? '[BRAND_NAME]') . ' Footwear Bangladesh')">
    <meta property="og:description" content="@yield('og_description', 'Handcrafted elegance engineered for cloud-walking comfort in Dhaka & beyond.')">
    <meta property="og:image" content="@yield('og_image', asset('images/banners/hero-heels.webp'))">
    <meta property="og:locale" content="en_US">

    <!-- Twitter Cards -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('og_title', ($storeSettings['shop_name'] ?? '[BRAND_NAME]') . ' Footwear Bangladesh')">
    <meta name="twitter:description" content="@yield('og_description', 'Handcrafted elegance engineered for cloud-walking comfort in Dhaka & beyond.')">
    <meta name="twitter:image" content="@yield('og_image', asset('images/banners/hero-heels.webp'))">

    <meta name="theme-color" content="#881337">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;0,700;1,500&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="flex min-h-full flex-col font-sans" x-data="{
    mobileMenuOpen: false,
    searchModalOpen: false,
    searchQuery: '',
    searchResults: [],
    isSearching: false,
    cartCount: {{ $cartCount ?? 0 }},
    toastMessage: '',
    showToast: false,
    activeCategoryTab: null,

    async performSearch() {
        if (this.searchQuery.trim().length < 2) {
            this.searchResults = [];
            return;
        }
        this.isSearching = true;
        try {
            const res = await fetch(`/api/search?q=${encodeURIComponent(this.searchQuery)}`);
            const data = await res.json();
            this.searchResults = data.results || [];
        } catch (e) {
            this.searchResults = [];
        } finally {
            this.isSearching = false;
        }
    },

    notify(message) {
        this.toastMessage = message;
        this.showToast = true;
        setTimeout(() => { this.showToast = false; }, 3500);
    },

    async quickAdd(variantId) {
        try {
            const res = await fetch('/api/cart/quick-add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                },
                body: JSON.stringify({ variant_id: variantId, quantity: 1 })
            });
            const data = await res.json();
            if (data.success) {
                this.cartCount = data.cart_count;
                this.notify('✓ Added to your shopping bag!');
                window.Livewire?.dispatch('cart-updated');
                window.Livewire?.dispatch('open-mini-cart');
            } else {
                this.notify('⚠ ' + (data.message || 'Could not add item.'));
            }
        } catch (err) {
            this.notify('⚠ Error adding to cart.');
        }
    }
}"
@keydown.escape="searchModalOpen = false; mobileMenuOpen = false"
@cart-count-updated.window="cartCount = $event.detail.count"
@show-toast.window="notify($event.detail.message)"
>

    <!-- Top Announcement Bar -->
    <div class="bg-rose-950 text-rose-100 text-xs py-2 px-4 transition-colors">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <p class="truncate flex items-center gap-2">
                <span class="inline-block w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                <span><strong>Free Home Delivery Inside Dhaka</strong> on orders over ৳{{ number_format($storeSettings['free_delivery_threshold'] ?? 3000) }}</span>
                <span class="hidden md:inline">• 🇧🇩 100% Handcrafted • 🔄 7 Days Size Exchange</span>
            </p>
            <div class="hidden sm:flex items-center gap-4 text-rose-200">
                <a href="tel:{{ $storeSettings['support_phone'] ?? '+8801700000000' }}" class="hover:text-white flex items-center gap-1 transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    <span>{{ $storeSettings['support_phone'] ?? '+880 1700-000000' }}</span>
                </a>
                <span class="text-rose-400">|</span>
                <a href="#outlets" class="hover:text-white transition">Store Outlets</a>
            </div>
        </div>
    </div>

    <!-- Sticky Main Header -->
    <header class="sticky top-0 z-30 bg-white/95 backdrop-blur-md border-b border-stone-200/80 shadow-xs transition-all">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-18 sm:h-20 gap-4">

                <!-- Left: Mobile Menu Toggle & Brand Logo -->
                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        class="lg:hidden p-2 -ml-2 text-slate-700 hover:text-rose-900 rounded-lg hover:bg-stone-100 transition focus:outline-hidden"
                        @click="mobileMenuOpen = true"
                        aria-label="Open mobile menu"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>

                    <!-- Brand Logo -->
                    <a href="{{ route('home') }}" class="group flex items-center gap-2.5">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-rose-900 to-rose-700 text-white flex items-center justify-center font-bold text-lg shadow-sm group-hover:scale-105 transition-transform">
                            👠
                        </div>
                        <div class="flex flex-col">
                            <span class="font-extrabold tracking-wider text-xl sm:text-2xl text-slate-900 group-hover:text-rose-900 transition-colors uppercase font-serif">
                                {{ $storeSettings['shop_name'] ?? '[BRAND_NAME]' }}
                            </span>
                            <span class="text-[9px] tracking-widest text-stone-500 uppercase -mt-1 font-semibold">
                                Handcrafted Footwear
                            </span>
                        </div>
                    </a>
                </div>

                <!-- Center: Desktop Search Bar -->
                <div class="hidden md:flex flex-1 max-w-md mx-6">
                    <div
                        class="relative w-full cursor-pointer group"
                        @click="searchModalOpen = true; $nextTick(() => $refs.searchInput?.focus())"
                    >
                        <div class="w-full flex items-center gap-2 pl-4 pr-3 py-2.5 rounded-full bg-stone-100/90 border border-stone-200 text-stone-500 text-sm hover:border-rose-800/40 hover:bg-white group-hover:shadow-xs transition">
                            <svg class="w-4 h-4 text-stone-400 group-hover:text-rose-900 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <span class="text-stone-500 font-normal">Search block heels, flats, mules, Boston clogs...</span>
                            <kbd class="ml-auto text-[10px] bg-stone-200 text-stone-600 px-2 py-0.5 rounded font-mono">⌘K</kbd>
                        </div>
                    </div>
                </div>

                <!-- Right: Actions (Search Mobile, Outlets, Cart) -->
                <div class="flex items-center gap-2 sm:gap-4">
                    <!-- Mobile Search Trigger -->
                    <button
                        type="button"
                        class="md:hidden p-2.5 text-slate-700 hover:text-rose-900 rounded-full hover:bg-stone-100 transition"
                        @click="searchModalOpen = true; $nextTick(() => $refs.searchInput?.focus())"
                        aria-label="Search footwear"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </button>

                    <!-- Track Order Link -->
                    <a href="{{ route('track-order') }}" class="hidden md:inline-flex items-center gap-1 text-xs font-semibold text-slate-700 hover:text-rose-900 px-2.5 py-1.5 rounded-lg hover:bg-stone-100 transition">
                        <span>🚚</span>
                        <span>Track Order</span>
                    </a>

                    <!-- Customer Account / Auth Link -->
                    @auth
                        <div class="relative group" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                            <a href="{{ route('account.dashboard') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-rose-950 bg-rose-50 hover:bg-rose-100 px-3 py-1.5 rounded-full transition">
                                <span>👤</span>
                                <span class="max-w-[100px] truncate">{{ Auth::user()->name }}</span>
                                <svg class="w-3 h-3 text-rose-800" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </a>
                            <div x-show="open" x-cloak class="absolute right-0 top-full pt-1.5 w-44 z-50">
                                <div class="bg-white rounded-2xl shadow-xl border border-stone-200 p-2 space-y-1 text-xs">
                                    <a href="{{ route('account.dashboard') }}" class="block px-3 py-2 rounded-xl text-slate-700 hover:bg-rose-50 hover:text-rose-950 font-semibold">
                                        Dashboard
                                    </a>
                                    <a href="{{ route('account.dashboard', ['tab' => 'orders']) }}" class="block px-3 py-2 rounded-xl text-slate-700 hover:bg-rose-50 hover:text-rose-950 font-semibold">
                                        My Orders
                                    </a>
                                    <a href="{{ route('account.dashboard', ['tab' => 'exchanges']) }}" class="block px-3 py-2 rounded-xl text-slate-700 hover:bg-rose-50 hover:text-rose-950 font-semibold">
                                        Size Exchanges
                                    </a>
                                    <form method="POST" action="{{ route('logout') }}" class="pt-1 border-t border-stone-100">
                                        @csrf
                                        <button type="submit" class="w-full text-left px-3 py-2 rounded-xl text-rose-600 hover:bg-rose-50 font-bold cursor-pointer">
                                            Sign Out
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="inline-flex items-center gap-1 text-xs font-bold text-slate-700 hover:text-rose-900 px-2.5 py-1.5 rounded-lg hover:bg-stone-100 transition">
                            <span>Sign In</span>
                        </a>
                    @endauth

                    <!-- Admin Portal Shortcut -->
                    <a href="/admin" class="hidden sm:inline-flex items-center text-xs font-semibold text-slate-500 hover:text-slate-800 px-2.5 py-1.5 rounded-md hover:bg-stone-100 transition" title="Staff / Admin Sign In">
                        Staff
                    </a>

                    <!-- Shopping Cart Icon with Live Count -->
                    <button
                        type="button"
                        @click="$dispatch('open-mini-cart')"
                        class="relative p-2.5 text-slate-800 hover:text-rose-900 rounded-full hover:bg-stone-100 transition flex items-center justify-center group cursor-pointer"
                        aria-label="Shopping Bag"
                    >
                        <svg class="w-6 h-6 group-hover:scale-105 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                        <span
                            class="absolute -top-1 -right-1 bg-rose-700 text-white text-[11px] font-bold h-5 w-5 rounded-full flex items-center justify-center shadow-xs"
                            x-text="cartCount"
                            x-show="cartCount > 0"
                        >{{ $cartCount ?? 0 }}</span>
                    </button>
                </div>
            </div>

            <!-- Desktop Category Navigation & Mega Menu -->
            <nav class="hidden lg:flex items-center justify-between border-t border-stone-100 py-2.5 text-sm font-medium text-slate-700">
                <div class="flex items-center gap-1">
                    <a href="{{ route('home') }}" class="px-3.5 py-1.5 rounded-full font-bold text-rose-950 bg-rose-50 hover:bg-rose-100 transition">
                        Home
                    </a>
                    <a href="{{ route('products.index') }}" class="px-3 py-1.5 rounded-lg font-semibold text-slate-700 hover:text-rose-950 hover:bg-stone-100 transition">
                        Shop All
                    </a>

                    @foreach(($categories ?? collect()) as $cat)
                        <div class="relative group" @mouseenter="activeCategoryTab = '{{ $cat->id }}'" @mouseleave="activeCategoryTab = null">
                            <a
                                href="{{ route('category.show', $cat->slug) }}"
                                class="px-3 py-1.5 rounded-lg hover:text-rose-950 hover:bg-stone-100 flex items-center gap-1 transition"
                            >
                                <span>{{ $cat->name }}</span>
                                @if($cat->children->isNotEmpty())
                                    <svg class="w-3.5 h-3.5 text-stone-400 group-hover:text-rose-900 transition-transform group-hover:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                @endif
                            </a>

                            <!-- Desktop Mega Menu Dropdown -->
                            @if($cat->children->isNotEmpty())
                                <div
                                    class="absolute left-0 top-full pt-2 w-80 invisible group-hover:visible opacity-0 group-hover:opacity-100 transition-all duration-200 z-40 transform translate-y-1 group-hover:translate-y-0"
                                >
                                    <div class="bg-white rounded-2xl shadow-xl border border-stone-200 p-4">
                                        <div class="flex items-center justify-between pb-2 mb-2 border-b border-stone-100">
                                            <span class="text-xs font-bold uppercase tracking-wider text-rose-950">{{ $cat->name }} Collection</span>
                                            <span class="text-[11px] text-stone-500 font-semibold">{{ $cat->products_count }} pairs</span>
                                        </div>
                                        <div class="space-y-1">
                                            @foreach($cat->children as $child)
                                                <a
                                                    href="{{ route('category.show', $child->slug) }}"
                                                    class="flex items-center justify-between px-3 py-2 rounded-lg text-xs text-slate-700 hover:text-rose-950 hover:bg-rose-50 font-medium transition"
                                                >
                                                    <span>{{ $child->name }}</span>
                                                    <span class="text-[10px] text-rose-800 bg-rose-100/60 px-2 py-0.5 rounded-full">Explore</span>
                                                </a>
                                            @endforeach
                                        </div>
                                        <div class="mt-3 pt-3 border-t border-stone-100">
                                            <a href="{{ route('category.show', $cat->slug) }}" class="block text-center text-xs font-bold text-rose-900 hover:underline">
                                                View All {{ $cat->name }} →
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="flex items-center gap-3 text-xs font-semibold">
                    <a href="#new-arrivals" class="text-rose-800 hover:text-rose-950 flex items-center gap-1 transition">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span>
                        <span>New Arrivals</span>
                    </a>
                    <span class="text-stone-300">•</span>
                    <a href="#best-selling" class="text-amber-800 hover:text-amber-950 flex items-center gap-1 transition">
                        <span>Best Selling</span>
                    </a>
                    <span class="text-stone-300">•</span>
                    <a href="#reviews" class="text-slate-600 hover:text-slate-900 transition">
                        Reviews
                    </a>
                </div>
            </nav>
        </div>
    </header>

    <!-- Mobile Slide-Out Drawer (Off-Canvas Menu) -->
    <div
        class="relative z-50 lg:hidden"
        x-show="mobileMenuOpen"
        x-cloak
        aria-modal="true"
    >
        <!-- Backdrop -->
        <div
            class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs transition-opacity"
            x-show="mobileMenuOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="mobileMenuOpen = false"
        ></div>

        <!-- Slide Drawer Panel -->
        <div
            class="fixed inset-y-0 left-0 max-w-xs w-full bg-white shadow-2xl flex flex-col z-50 overflow-y-auto"
            x-show="mobileMenuOpen"
            x-transition:enter="transform transition ease-out duration-300"
            x-transition:enter-start="-translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transform transition ease-in duration-200"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="-translate-x-full"
        >
            <!-- Drawer Header -->
            <div class="p-4 border-b border-stone-200 flex items-center justify-between bg-rose-950 text-white">
                <div class="flex items-center gap-2">
                    <span class="text-xl">👠</span>
                    <span class="font-bold text-lg tracking-wider uppercase font-serif">{{ $storeSettings['shop_name'] ?? '[BRAND_NAME]' }}</span>
                </div>
                <button
                    type="button"
                    class="p-2 text-rose-200 hover:text-white rounded-lg"
                    @click="mobileMenuOpen = false"
                    aria-label="Close menu"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Drawer Search -->
            <div class="p-4 border-b border-stone-100 bg-stone-50">
                <button
                    type="button"
                    class="w-full flex items-center gap-2 px-3 py-2 rounded-lg bg-white border border-stone-300 text-stone-500 text-sm shadow-2xs text-left"
                    @click="mobileMenuOpen = false; searchModalOpen = true; $nextTick(() => $refs.searchInput?.focus())"
                >
                    <svg class="w-4 h-4 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <span>Search footwear...</span>
                </button>
            </div>

            <!-- Drawer Category Accordions -->
            <div class="p-4 space-y-1 flex-1">
                <p class="text-[11px] font-bold text-stone-400 uppercase tracking-wider px-3 mb-2">Footwear Categories</p>

                @foreach(($categories ?? collect()) as $cat)
                    <div x-data="{ expanded: false }" class="border-b border-stone-100 last:border-b-0 pb-1">
                        <div class="flex items-center justify-between">
                            <a
                                href="{{ route('category.show', $cat->slug) }}"
                                class="flex-1 py-2.5 px-3 rounded-lg text-sm font-semibold text-slate-800 hover:text-rose-950 hover:bg-stone-50 flex items-center justify-between"
                                @click="mobileMenuOpen = false"
                            >
                                <span>{{ $cat->name }}</span>
                                <span class="text-xs text-stone-400 font-normal">({{ $cat->products_count }})</span>
                            </a>
                            @if($cat->children->isNotEmpty())
                                <button
                                    type="button"
                                    class="p-2 text-stone-400 hover:text-slate-800"
                                    @click="expanded = !expanded"
                                    aria-label="Toggle subcategories"
                                >
                                    <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': expanded }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                            @endif
                        </div>

                        @if($cat->children->isNotEmpty())
                            <div x-show="expanded" x-collapse class="pl-6 pr-2 py-1 space-y-1 bg-stone-50/70 rounded-lg mb-2">
                                @foreach($cat->children as $child)
                                    <a
                                        href="{{ route('category.show', $child->slug) }}"
                                        class="block py-1.5 px-3 text-xs text-slate-600 hover:text-rose-900 rounded-md transition"
                                        @click="mobileMenuOpen = false"
                                    >
                                        • {{ $child->name }}
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach

                <div class="pt-4 mt-4 border-t border-stone-200 space-y-2">
                    <p class="text-[11px] font-bold text-stone-400 uppercase tracking-wider px-3">Customer Account & Services</p>
                    <a href="{{ route('track-order') }}" class="flex items-center gap-2.5 py-2 px-3 rounded-lg text-sm text-slate-700 hover:bg-stone-50" @click="mobileMenuOpen = false">
                        <span class="text-base">🚚</span>
                        <span>Track My Order</span>
                    </a>
                    @auth
                        <a href="{{ route('account.dashboard') }}" class="flex items-center gap-2.5 py-2 px-3 rounded-lg text-sm font-bold text-rose-950 bg-rose-50" @click="mobileMenuOpen = false">
                            <span class="text-base">👤</span>
                            <span>My Account ({{ Auth::user()->name }})</span>
                        </a>
                        <form method="POST" action="{{ route('logout') }}" class="px-3 pt-1">
                            @csrf
                            <button type="submit" class="text-xs font-bold text-rose-600 hover:underline">
                                Sign Out
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="flex items-center gap-2.5 py-2 px-3 rounded-lg text-sm font-bold text-rose-900 bg-rose-50" @click="mobileMenuOpen = false">
                            <span class="text-base">🔑</span>
                            <span>Sign In / Create Account</span>
                        </a>
                    @endauth

                    <p class="text-[11px] font-bold text-stone-400 uppercase tracking-wider px-3 pt-2">Help & Quick Links</p>
                    <a href="#outlets" class="flex items-center gap-2.5 py-2 px-3 rounded-lg text-sm text-slate-700 hover:bg-stone-50" @click="mobileMenuOpen = false">
                        <svg class="w-4 h-4 text-rose-800" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>Store Outlets (6 Locations)</span>
                    </a>
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $storeSettings['whatsapp_number'] ?? '+8801700000000') }}" target="_blank" class="flex items-center gap-2.5 py-2 px-3 rounded-lg text-sm text-emerald-700 hover:bg-emerald-50">
                        <span class="text-base">💬</span>
                        <span class="font-semibold">WhatsApp Order & Inquiries</span>
                    </a>
                    <a href="/admin" class="flex items-center gap-2.5 py-2 px-3 rounded-lg text-sm text-slate-500 hover:bg-stone-50">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        <span>Staff Login</span>
                    </a>
                </div>
            </div>

            <!-- Drawer Footer -->
            <div class="p-4 border-t border-stone-200 bg-stone-50 text-xs text-stone-500 text-center">
                <p>Hotline: {{ $storeSettings['support_phone'] ?? '+880 1700-000000' }}</p>
                <p class="text-[10px] text-stone-400 mt-1">© {{ date('Y') }} {{ $storeSettings['shop_name'] ?? '[BRAND_NAME]' }}. All rights reserved.</p>
            </div>
        </div>
    </div>

    <!-- Search Overlay / Modal (Instant Debounced Search) -->
    <div
        class="fixed inset-0 z-50 overflow-y-auto p-4 sm:p-6 md:p-20"
        x-show="searchModalOpen"
        x-cloak
        role="dialog"
        aria-modal="true"
    >
        <div
            class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs transition-opacity"
            x-show="searchModalOpen"
            x-transition.opacity
            @click="searchModalOpen = false"
        ></div>

        <div
            class="relative mx-auto max-w-2xl transform rounded-2xl bg-white p-4 sm:p-6 shadow-2xl transition-all border border-stone-200"
            x-show="searchModalOpen"
            x-transition
        >
            <!-- Search Input -->
            <div class="relative flex items-center">
                <svg class="pointer-events-none absolute left-3.5 h-5 w-5 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input
                    type="text"
                    x-ref="searchInput"
                    x-model="searchQuery"
                    @input.debounce.250ms="performSearch()"
                    placeholder="Search by shoe name, category (e.g. heels, flats, mules)..."
                    class="h-12 w-full rounded-xl bg-stone-50 pl-11 pr-10 text-sm text-slate-900 placeholder:text-stone-400 focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-rose-800/40 border border-stone-200"
                >
                <button
                    type="button"
                    class="absolute right-3 text-stone-400 hover:text-slate-700"
                    @click="searchModalOpen = false"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Search Quick Suggestions -->
            <div class="mt-3 flex items-center gap-2 text-xs text-stone-500 overflow-x-auto no-scrollbar py-1">
                <span class="font-semibold text-stone-400">Popular:</span>
                <button type="button" @click="searchQuery = 'Velvet'; performSearch()" class="px-2.5 py-1 rounded-full bg-stone-100 hover:bg-rose-50 hover:text-rose-900 transition">Velvet Block Heels</button>
                <button type="button" @click="searchQuery = 'Clog'; performSearch()" class="px-2.5 py-1 rounded-full bg-stone-100 hover:bg-rose-50 hover:text-rose-900 transition">Boston Clogs</button>
                <button type="button" @click="searchQuery = 'Kolhapuri'; performSearch()" class="px-2.5 py-1 rounded-full bg-stone-100 hover:bg-rose-50 hover:text-rose-900 transition">Kolhapuri</button>
                <button type="button" @click="searchQuery = 'Mules'; performSearch()" class="px-2.5 py-1 rounded-full bg-stone-100 hover:bg-rose-50 hover:text-rose-900 transition">Mules</button>
            </div>

            <!-- Search Loading State -->
            <div x-show="isSearching" class="py-8 text-center text-sm text-stone-500">
                <span class="inline-block animate-spin mr-2">⏳</span> Searching catalog...
            </div>

            <!-- Search Results List -->
            <div x-show="!isSearching && searchResults.length > 0" class="mt-4 divide-y divide-stone-100 max-h-96 overflow-y-auto">
                <template x-for="item in searchResults" :key="item.id">
                    <a
                        :href="'#product-' + item.slug"
                        @click="searchModalOpen = false"
                        class="flex items-center gap-4 py-3 px-2 rounded-xl hover:bg-rose-50/60 transition group"
                    >
                        <img :src="item.image_url" :alt="item.name" class="w-14 h-14 rounded-lg object-cover bg-stone-100 border border-stone-200">
                        <div class="flex-1 min-w-0">
                            <span class="text-[10px] font-bold uppercase text-rose-800 tracking-wider" x-text="item.category"></span>
                            <h4 class="text-sm font-semibold text-slate-900 group-hover:text-rose-900 truncate" x-text="item.name"></h4>
                            <div class="flex items-center gap-2 mt-0.5">
                                <span class="text-sm font-bold text-rose-950">৳ <span x-text="Number(item.price).toLocaleString()"></span></span>
                                <span x-show="item.has_discount" class="text-xs text-stone-400 line-through">৳ <span x-text="Number(item.original_price).toLocaleString()"></span></span>
                            </div>
                        </div>
                        <span class="text-xs font-semibold text-rose-800 bg-rose-100/70 px-2.5 py-1 rounded-full">View</span>
                    </a>
                </template>
            </div>

            <!-- Empty Results State -->
            <div x-show="!isSearching && searchResults.length === 0 && searchQuery.trim().length >= 2" class="py-8 text-center text-sm text-stone-500">
                No pairs found matching "<strong x-text="searchQuery"></strong>". Try searching for "Heels", "Flats", or "Slides".
            </div>
        </div>
    </div>

    <!-- Main Storefront Content -->
    <main class="flex-1">
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    <!-- Global Floating Toast Notification -->
    <div
        class="fixed bottom-20 md:bottom-8 right-4 sm:right-8 z-50 transform transition-all duration-300"
        x-show="showToast"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-4"
        x-cloak
    >
        <div class="bg-slate-900 text-white text-sm font-medium py-3 px-5 rounded-2xl shadow-2xl flex items-center gap-3 border border-slate-700">
            <span x-text="toastMessage"></span>
        </div>
    </div>

    <!-- Mobile Bottom Navigation Bar (Sticky at viewport bottom) -->
    <nav class="md:hidden fixed bottom-0 inset-x-0 z-40 bg-white/95 backdrop-blur-md border-t border-stone-200 shadow-lg pb-safe">
        <div class="grid grid-cols-5 h-16 text-[10px] font-semibold text-stone-600">
            <!-- Home Tab -->
            <a href="{{ route('home') }}" class="flex flex-col items-center justify-center gap-1 text-rose-900">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span>Home</span>
            </a>

            <!-- Categories Tab -->
            <button type="button" @click="mobileMenuOpen = true" class="flex flex-col items-center justify-center gap-1 hover:text-rose-900">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
                <span>Browse</span>
            </button>

            <!-- Search Tab -->
            <button type="button" @click="searchModalOpen = true; $nextTick(() => $refs.searchInput?.focus())" class="flex flex-col items-center justify-center gap-1 hover:text-rose-900">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <span>Search</span>
            </button>

            <!-- Outlets Tab -->
            <a href="#outlets" class="flex flex-col items-center justify-center gap-1 hover:text-rose-900">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span>Outlets</span>
            </a>

            <!-- Bag / Cart Tab -->
            <button
                type="button"
                @click="$dispatch('open-mini-cart')"
                class="relative flex flex-col items-center justify-center gap-1 hover:text-rose-900 cursor-pointer"
            >
                <div class="relative">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    <span
                        class="absolute -top-1.5 -right-2 bg-rose-700 text-white text-[9px] font-bold h-4 w-4 rounded-full flex items-center justify-center"
                        x-text="cartCount"
                        x-show="cartCount > 0"
                    >{{ $cartCount ?? 0 }}</span>
                </div>
                <span>Bag</span>
            </button>
        </div>
    </nav>

    <!-- Public Storefront Footer -->
    <footer class="bg-stone-900 text-stone-300 pt-16 pb-24 md:pb-12 border-t border-stone-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10">

                <!-- Col 1: Brand Story & Values -->
                <div class="space-y-4">
                    <div class="flex items-center gap-2 text-white">
                        <span class="text-2xl">👠</span>
                        <span class="font-extrabold text-2xl tracking-wider uppercase font-serif">{{ $storeSettings['shop_name'] ?? '[BRAND_NAME]' }}</span>
                    </div>
                    <p class="text-sm text-stone-400 leading-relaxed">
                        Handcrafted luxury footwear designed specifically for Bangladeshi women. Celebrating traditional master craftsmanship with modern orthopedic cushion footbeds.
                    </p>
                    <div class="space-y-1.5 text-xs text-stone-400">
                        <p class="flex items-center gap-2">
                            <span class="text-rose-400 font-bold">📍</span>
                            <span>Flagship: House 42, Road 27, Dhanmondi, Dhaka</span>
                        </p>
                        <p class="flex items-center gap-2">
                            <span class="text-rose-400 font-bold">📞</span>
                            <a href="tel:{{ $storeSettings['support_phone'] ?? '+8801700000000' }}" class="hover:text-white transition">{{ $storeSettings['support_phone'] ?? '+880 1700-000000' }}</a>
                        </p>
                        <p class="flex items-center gap-2">
                            <span class="text-rose-400 font-bold">✉️</span>
                            <a href="mailto:{{ $storeSettings['support_email'] ?? 'care@brandfootwear.com' }}" class="hover:text-white transition">{{ $storeSettings['support_email'] ?? 'care@brandfootwear.com' }}</a>
                        </p>
                    </div>
                </div>

                <!-- Col 2: Category Catalog Links -->
                <div>
                    <h3 class="text-xs font-bold text-white uppercase tracking-widest mb-4">Footwear Categories</h3>
                    <ul class="space-y-2.5 text-sm text-stone-400">
                        @foreach(($categories ?? collect())->take(6) as $c)
                            <li>
                                <a href="{{ route('category.show', $c->slug) }}" class="hover:text-white hover:translate-x-1 inline-block transition">
                                    {{ $c->name }}
                                </a>
                            </li>
                        @endforeach
                        <li>
                            <a href="#new-arrivals" class="text-rose-400 font-semibold hover:text-rose-300">New Festive Arrivals →</a>
                        </li>
                    </ul>
                </div>

                <!-- Col 3: Customer Care & Policies -->
                <div>
                    <h3 class="text-xs font-bold text-white uppercase tracking-widest mb-4">Customer Care</h3>
                    <ul class="space-y-2.5 text-sm text-stone-400">
                        <li><a href="#delivery-info" class="hover:text-white transition">Delivery Charges & Timelines</a></li>
                        <li><a href="#exchange-policy" class="hover:text-white transition">7 Days Size Exchange Policy</a></li>
                        <li><a href="#outlets" class="hover:text-white transition">Find Nearest Outlet</a></li>
                        <li><a href="#care-guide" class="hover:text-white transition">Shoe Care & Maintenance</a></li>
                        <li><a href="/admin" class="hover:text-white transition">Staff Management Portal</a></li>
                    </ul>
                </div>

                <!-- Col 4: Retail Outlets & Social Channels -->
                <div class="space-y-4">
                    <h3 class="text-xs font-bold text-white uppercase tracking-widest mb-4">Connect & Outlets</h3>
                    <p class="text-xs text-stone-400">Visit our boutiques in Dhanmondi, Banani, Uttara, Bashundhara City, Jamuna Future Park & Chattogram GEC.</p>

                    <!-- Social Icons -->
                    <div class="flex items-center gap-3">
                        <a href="{{ $storeSettings['facebook_url'] ?? 'https://facebook.com' }}" target="_blank" rel="noopener" class="w-9 h-9 rounded-full bg-stone-800 hover:bg-rose-900 text-white flex items-center justify-center transition shadow-xs" aria-label="Facebook">
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </a>
                        <a href="{{ $storeSettings['instagram_url'] ?? 'https://instagram.com' }}" target="_blank" rel="noopener" class="w-9 h-9 rounded-full bg-stone-800 hover:bg-rose-900 text-white flex items-center justify-center transition shadow-xs" aria-label="Instagram">
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                        </a>
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $storeSettings['whatsapp_number'] ?? '+8801700000000') }}" target="_blank" rel="noopener" class="w-9 h-9 rounded-full bg-emerald-600 hover:bg-emerald-500 text-white flex items-center justify-center transition shadow-xs" aria-label="WhatsApp Order">
                            <span class="text-sm">💬</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Bottom Bar: Accepted Payment Methods & Copyright -->
            <div class="mt-12 pt-8 border-t border-stone-800 flex flex-col md:flex-row items-center justify-between gap-6 text-xs text-stone-500">
                <div class="flex items-center gap-2">
                    <span>© {{ date('Y') }} {{ $storeSettings['shop_name'] ?? '[BRAND_NAME]' }} Ltd. Made with ❤️ in Dhaka, Bangladesh.</span>
                </div>

                <!-- Accepted Payment Badges -->
                <div class="flex items-center flex-wrap gap-2">
                    <span class="text-[11px] text-stone-400 font-semibold mr-1">Accepted Payments:</span>
                    <span class="px-2.5 py-1 rounded bg-stone-800 text-rose-300 font-bold text-[11px] border border-stone-700">bKash</span>
                    <span class="px-2.5 py-1 rounded bg-stone-800 text-orange-400 font-bold text-[11px] border border-stone-700">Nagad</span>
                    <span class="px-2.5 py-1 rounded bg-stone-800 text-purple-300 font-bold text-[11px] border border-stone-700">Rocket</span>
                    <span class="px-2.5 py-1 rounded bg-stone-800 text-blue-300 font-bold text-[11px] border border-stone-700">VISA</span>
                    <span class="px-2.5 py-1 rounded bg-stone-800 text-amber-300 font-bold text-[11px] border border-stone-700">Mastercard</span>
                    <span class="px-2.5 py-1 rounded bg-stone-800 text-emerald-400 font-bold text-[11px] border border-stone-700">Cash on Delivery</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Livewire Mini-Cart Drawer Component -->
    <livewire:cart.mini-cart />

    @livewireScripts
</body>
</html>
