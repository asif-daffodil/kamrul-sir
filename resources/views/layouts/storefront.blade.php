<!DOCTYPE html>
<html lang="en" class="h-full bg-stone-50 text-slate-800 antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', ($storeSettings['shop_name'] ?? 'Heel Bari') . ' | Handcrafted Women\'s Footwear Bangladesh')</title>
    <meta name="description" content="@yield('meta_description', 'Discover handcrafted women\'s footwear in Bangladesh: elegant block heels, cushioned daily flats, braided slides, modern mules, Boston clogs & festive Kolhapuri chappals. Cash on Delivery across all 64 districts.')">
    <meta name="robots" content="@yield('meta_robots', 'index, follow, max-image-preview:large')">
    <link rel="canonical" href="@yield('canonical_url', url()->current())">

    <!-- Favicon & App Icons -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="64x64" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">

    <!-- OpenGraph / Facebook Meta -->
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:site_name" content="{{ $storeSettings['shop_name'] ?? 'Heel Bari' }}">
    <meta property="og:url" content="@yield('canonical_url', url()->current())">
    <meta property="og:title" content="@yield('og_title', ($storeSettings['shop_name'] ?? 'Heel Bari') . ' Footwear Bangladesh')">
    <meta property="og:description" content="@yield('og_description', 'Handcrafted elegance engineered for cloud-walking comfort in Dhaka & beyond.')">
    <meta property="og:image" content="@yield('og_image', asset('images/logo/heel-bari-logo.webp'))">
    <meta property="og:locale" content="en_US">

    <!-- Twitter Cards -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('og_title', ($storeSettings['shop_name'] ?? 'Heel Bari') . ' Footwear Bangladesh')">
    <meta name="twitter:description" content="@yield('og_description', 'Handcrafted elegance engineered for cloud-walking comfort in Dhaka & beyond.')">
    <meta name="twitter:image" content="@yield('og_image', asset('images/logo/heel-bari-logo.webp'))">

    <meta name="theme-color" content="#881337">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;0,700;1,500;1,600&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="flex min-h-full flex-col font-sans selection:bg-rose-900 selection:text-white" x-data="{
    mobileMenuOpen: false,
    mobileMenuTab: 'categories',
    searchModalOpen: false,
    wishlistDrawerOpen: false,
    searchQuery: '',
    searchResults: [],
    isSearching: false,
    cartCount: {{ $cartCount ?? 0 }},
    toastMessage: '',
    showToast: false,
    activeMegaMenu: null,
    megaMenuTimer: null,

    // LocalStorage Wishlist
    wishlist: JSON.parse(localStorage.getItem('heelbari_wishlist') || '[]'),

    isWishlisted(productId) {
        return this.wishlist.includes(productId);
    },

    toggleWishlist(productId, productName = 'This pair') {
        if (this.isWishlisted(productId)) {
            this.wishlist = this.wishlist.filter(id => id !== productId);
            this.notify('Removed ' + productName + ' from Wishlist');
        } else {
            this.wishlist.push(productId);
            this.notify('♥ Added ' + productName + ' to Wishlist!');
        }
        localStorage.setItem('heelbari_wishlist', JSON.stringify(this.wishlist));
    },

    get wishlistCount() {
        return this.wishlist.length;
    },

    openMega(menu) {
        if (this.megaMenuTimer) clearTimeout(this.megaMenuTimer);
        this.activeMegaMenu = menu;
    },

    closeMega() {
        this.megaMenuTimer = setTimeout(() => {
            this.activeMegaMenu = null;
        }, 180);
    },

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
@keydown.escape="searchModalOpen = false; mobileMenuOpen = false; wishlistDrawerOpen = false; activeMegaMenu = null"
@cart-count-updated.window="cartCount = $event.detail.count"
@show-toast.window="notify($event.detail.message)"
>

    <!-- 1. TOP PROMO BAR (Admin Editable: Free Delivery Threshold, COD, Extra Discount on Prepaid) -->
    @if($storeSettings['promo_bar_enabled'] ?? true)
        <aside class="bg-gradient-to-r from-rose-950 via-[#700F2B] to-stone-950 text-rose-100 text-xs py-2.5 px-4 transition-colors border-b border-rose-900/40">
            <div class="max-w-7xl mx-auto flex items-center justify-between gap-4">
                <div class="flex items-center gap-2.5 overflow-hidden">
                    <span class="inline-block w-2 h-2 rounded-full bg-amber-400 animate-pulse shrink-0"></span>
                    <p class="truncate font-medium text-[11px] sm:text-xs">
                        @if(!empty($storeSettings['promo_bar_text']))
                            {{ $storeSettings['promo_bar_text'] }}
                        @else
                            <span>Free Home Delivery Inside Dhaka on orders over <strong>৳{{ number_format($storeSettings['free_delivery_threshold'] ?? 3000) }}</strong></span>
                            <span class="text-rose-400">•</span>
                            <span>Cash on Delivery in all 64 districts</span>
                            <span class="text-rose-400 hidden sm:inline">•</span>
                            <span class="hidden sm:inline font-bold text-amber-300">Extra {{ $storeSettings['prepaid_discount_percent'] ?? 5 }}% OFF on bKash / Card Prepaid</span>
                        @endif
                    </p>
                </div>

                <div class="hidden md:flex items-center gap-4 text-[11px] text-rose-200 shrink-0">
                    <a href="tel:{{ $storeSettings['support_phone'] ?? '+8801700000000' }}" class="hover:text-white flex items-center gap-1.5 transition">
                        <svg class="w-3.5 h-3.5 text-rose-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        <span>{{ $storeSettings['support_phone'] ?? '+880 1700-000000' }}</span>
                    </a>
                    <span class="text-rose-400">|</span>
                    <a href="#outlets" class="hover:text-white transition">6 Outlets in BD</a>
                </div>
            </div>
        </aside>
    @endif

    <!-- 2. STICKY MAIN HEADER -->
    <header class="sticky top-0 z-30 bg-white/95 backdrop-blur-md border-b border-stone-200/80 shadow-xs transition-all">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-18 sm:h-20 gap-3 md:gap-6">

                <!-- Left: Mobile Menu Toggle & Brand Logo -->
                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        class="lg:hidden p-2.5 -ml-2 text-slate-800 hover:text-rose-950 rounded-xl hover:bg-stone-100 transition focus:outline-hidden cursor-pointer"
                        @click="mobileMenuOpen = true"
                        aria-label="Open navigation menu"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>

                    <!-- Authentic Brand Logo -->
                    <a href="{{ route('home') }}" class="group flex items-center py-1 focus:outline-hidden" aria-label="Heel Bari Home">
                        <img
                            src="{{ asset('images/logo/heel-bari-logo.webp') }}"
                            alt="Heel Bari Handcrafted Footwear"
                            class="h-9 sm:h-10 md:h-11 w-auto object-contain transition-transform duration-200 group-hover:scale-[1.02]"
                            loading="eager"
                        >
                    </a>
                </div>

                <!-- Center: Desktop Search Trigger Bar -->
                <div class="hidden md:flex flex-1 max-w-lg mx-2 lg:mx-6">
                    <div
                        class="relative w-full cursor-pointer group"
                        @click="searchModalOpen = true; $nextTick(() => $refs.searchInput?.focus())"
                    >
                        <div class="w-full flex items-center gap-2.5 pl-4 pr-3 py-2.5 rounded-full bg-stone-100/90 border border-stone-200 text-stone-500 text-xs sm:text-sm hover:border-rose-900/40 hover:bg-white group-hover:shadow-xs transition">
                            <svg class="w-4 h-4 text-stone-400 group-hover:text-rose-900 transition-colors shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <span class="text-stone-500 font-normal truncate">Search block heels, daily flats, mules, Boston clogs...</span>
                            <kbd class="ml-auto text-[10px] bg-stone-200 text-stone-600 px-2 py-0.5 rounded font-mono shrink-0">⌘K</kbd>
                        </div>
                    </div>
                </div>

                <!-- Right: Header Actions (Search Mobile, Track, Wishlist, Account, Cart) -->
                <div class="flex items-center gap-1.5 sm:gap-3">
                    <!-- Mobile Search Trigger -->
                    <button
                        type="button"
                        class="md:hidden p-2 text-slate-700 hover:text-rose-950 rounded-full hover:bg-stone-100 transition cursor-pointer"
                        @click="searchModalOpen = true; $nextTick(() => $refs.searchInput?.focus())"
                        aria-label="Search footwear"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </button>

                    <!-- Track Order Link -->
                    <a href="{{ route('track-order') }}" class="hidden lg:inline-flex items-center gap-1.5 text-xs font-semibold text-slate-700 hover:text-rose-950 px-2.5 py-1.5 rounded-lg hover:bg-stone-100 transition">
                        <span>🚚</span>
                        <span>Track</span>
                    </a>

                    <!-- Wishlist Icon with Live Counter -->
                    <button
                        type="button"
                        @click="wishlistDrawerOpen = true"
                        class="relative p-2 text-slate-700 hover:text-rose-950 rounded-full hover:bg-stone-100 transition flex items-center justify-center group cursor-pointer"
                        aria-label="Wishlist"
                        title="Saved to Wishlist"
                    >
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                        <span
                            class="absolute -top-1 -right-1 bg-[#C5A059] text-white text-[10px] font-bold h-4 w-4 rounded-full flex items-center justify-center shadow-xs"
                            x-text="wishlistCount"
                            x-show="wishlistCount > 0"
                            x-cloak
                        >0</span>
                    </button>

                    <!-- Account / Auth Dropdown -->
                    @auth
                        <div class="relative group" x-data="{ userMenuOpen: false }" @mouseenter="userMenuOpen = true" @mouseleave="userMenuOpen = false">
                            <a href="{{ route('account.dashboard') }}" class="inline-flex items-center gap-1 text-xs font-bold text-rose-950 bg-rose-50 hover:bg-rose-100 px-3 py-1.5 rounded-full transition">
                                <span>👤</span>
                                <span class="max-w-[80px] sm:max-w-[110px] truncate hidden sm:inline">{{ Auth::user()->name }}</span>
                                <svg class="w-3 h-3 text-rose-800" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </a>
                            <div x-show="userMenuOpen" x-cloak class="absolute right-0 top-full pt-1.5 w-48 z-50">
                                <div class="bg-white rounded-2xl shadow-xl border border-stone-200 p-2 space-y-1 text-xs">
                                    <div class="px-3 py-2 border-b border-stone-100 mb-1">
                                        <p class="font-bold text-slate-900 truncate">{{ Auth::user()->name }}</p>
                                        <p class="text-[10px] text-stone-500 truncate">{{ Auth::user()->email }}</p>
                                    </div>
                                    <a href="{{ route('account.dashboard') }}" class="block px-3 py-2 rounded-xl text-slate-700 hover:bg-rose-50 hover:text-rose-950 font-semibold">
                                        My Dashboard
                                    </a>
                                    <a href="{{ route('account.dashboard', ['tab' => 'orders']) }}" class="block px-3 py-2 rounded-xl text-slate-700 hover:bg-rose-50 hover:text-rose-950 font-semibold">
                                        Order History
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
                        <a href="{{ route('login') }}" class="inline-flex items-center gap-1 text-xs font-bold text-slate-700 hover:text-rose-950 px-2.5 py-1.5 rounded-lg hover:bg-stone-100 transition">
                            <span>Sign In</span>
                        </a>
                    @endauth

                    <!-- Shopping Cart Icon with Live Count -->
                    <button
                        id="header-cart-button"
                        type="button"
                        @click="$dispatch('open-mini-cart')"
                        class="relative p-2 sm:p-2.5 text-slate-800 hover:text-rose-950 rounded-full hover:bg-stone-100 transition flex items-center justify-center group cursor-pointer"
                        aria-label="Shopping Bag"
                    >
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 group-hover:scale-105 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                        <span
                            class="absolute -top-1 -right-1 bg-rose-800 text-white text-[10px] font-bold h-4 sm:h-5 w-4 sm:w-5 rounded-full flex items-center justify-center shadow-xs"
                            x-text="cartCount"
                            x-show="cartCount > 0"
                        >{{ $cartCount ?? 0 }}</span>
                    </button>
                </div>
            </div>

            <!-- 3. DESKTOP MEGA MENU NAVIGATION BAR -->
            <nav class="hidden lg:flex items-center justify-between border-t border-stone-100 py-2.5 text-xs sm:text-sm font-medium text-slate-700 relative">
                <div class="flex items-center gap-1">
                    <a href="{{ route('home') }}" class="px-3.5 py-1.5 rounded-full font-bold text-rose-950 bg-rose-50 hover:bg-rose-100 transition">
                        Home
                    </a>
                    <a href="{{ route('products.index') }}" class="px-3 py-1.5 rounded-lg font-semibold text-slate-700 hover:text-rose-950 hover:bg-stone-100 transition">
                        All Footwear
                    </a>

                    <!-- MEGA MENU ITEM 1: CATEGORIES -->
                    <div class="relative" @mouseenter="openMega('categories')" @mouseleave="closeMega()">
                        <button
                            type="button"
                            class="px-3 py-1.5 rounded-lg hover:text-rose-950 hover:bg-stone-100 flex items-center gap-1 transition font-semibold cursor-pointer"
                            :class="activeMegaMenu === 'categories' ? 'text-rose-950 bg-stone-100' : ''"
                        >
                            <span>Categories</span>
                            <svg class="w-3.5 h-3.5 text-stone-400 transition-transform duration-200" :class="activeMegaMenu === 'categories' ? 'rotate-180 text-rose-900' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>

                        <!-- Mega Dropdown: Categories -->
                        <div
                            x-show="activeMegaMenu === 'categories'"
                            x-cloak
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 translate-y-2"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 translate-y-2"
                            class="absolute left-0 top-full pt-2 w-[720px] z-50"
                        >
                            <div class="bg-white rounded-3xl shadow-2xl border border-stone-200 p-6 grid grid-cols-3 gap-6">
                                <!-- Col 1: Heels & Wedges -->
                                <div>
                                    <h4 class="text-xs font-bold uppercase tracking-wider text-rose-950 pb-2 mb-2 border-b border-rose-100">Heels & Elevation</h4>
                                    <ul class="space-y-1.5 text-xs text-stone-600">
                                        <li><a href="{{ route('category.show', 'block-heels') }}" class="hover:text-rose-950 hover:translate-x-1 inline-block transition">Block Heels (2"–3")</a></li>
                                        <li><a href="{{ route('category.show', 'kitten-heels') }}" class="hover:text-rose-950 hover:translate-x-1 inline-block transition">Kitten Heels (1.5"–2")</a></li>
                                        <li><a href="{{ route('category.show', 'stilettos') }}" class="hover:text-rose-950 hover:translate-x-1 inline-block transition">Bridal Stilettos</a></li>
                                        <li><a href="{{ route('category.show', 'heels') }}" class="hover:text-rose-950 hover:translate-x-1 inline-block transition">All Heels Collection</a></li>
                                        <li><a href="{{ route('category.show', 'wedges') }}" class="hover:text-rose-950 hover:translate-x-1 inline-block transition">Espadrille & Cork Wedges</a></li>
                                    </ul>
                                </div>

                                <!-- Col 2: Flats, Slides & Clogs -->
                                <div>
                                    <h4 class="text-xs font-bold uppercase tracking-wider text-rose-950 pb-2 mb-2 border-b border-rose-100">Flats, Slides & Clogs</h4>
                                    <ul class="space-y-1.5 text-xs text-stone-600">
                                        <li><a href="{{ route('category.show', 'ballet-flats') }}" class="hover:text-rose-950 hover:translate-x-1 inline-block transition">Cushioned Ballet Flats</a></li>
                                        <li><a href="{{ route('category.show', 'pointed-flats') }}" class="hover:text-rose-950 hover:translate-x-1 inline-block transition">Pointed Toe Work Flats</a></li>
                                        <li><a href="{{ route('category.show', 'boston-clogs') }}" class="hover:text-rose-950 hover:translate-x-1 inline-block transition">Suede Boston Clogs</a></li>
                                        <li><a href="{{ route('category.show', 'slides') }}" class="hover:text-rose-950 hover:translate-x-1 inline-block transition">Braided & H-Strap Slides</a></li>
                                        <li><a href="{{ route('category.show', 'mules') }}" class="hover:text-rose-950 hover:translate-x-1 inline-block transition">Closed-Toe & Heeled Mules</a></li>
                                        <li><a href="{{ route('category.show', 'chappal') }}" class="hover:text-rose-950 hover:translate-x-1 inline-block transition">Ethnic Kolhapuri Chappals</a></li>
                                    </ul>
                                </div>

                                <!-- Col 3: Visual Editorial Card -->
                                <div class="bg-stone-50 rounded-2xl p-4 border border-stone-200/80 flex flex-col justify-between">
                                    <div>
                                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-[#C5A059]">Heel Bari Signature</span>
                                        <h5 class="text-sm font-bold text-slate-900 font-serif mt-1">Cloud Cushion Sole</h5>
                                        <p class="text-[11px] text-stone-500 mt-1 leading-relaxed">
                                            3-layer orthopedic memory foam footbed engineered for zero foot pain across Dhaka celebrations.
                                        </p>
                                    </div>
                                    <a href="{{ route('products.index') }}" class="mt-4 inline-flex items-center gap-1 text-xs font-bold text-rose-900 hover:text-rose-700">
                                        <span>Browse Full Catalog</span>
                                        <span>→</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- MEGA MENU ITEM 2: SHOP BY OCCASION -->
                    <div class="relative" @mouseenter="openMega('occasions')" @mouseleave="closeMega()">
                        <button
                            type="button"
                            class="px-3 py-1.5 rounded-lg hover:text-rose-950 hover:bg-stone-100 flex items-center gap-1 transition font-semibold cursor-pointer"
                            :class="activeMegaMenu === 'occasions' ? 'text-rose-950 bg-stone-100' : ''"
                        >
                            <span>Shop by Occasion</span>
                            <svg class="w-3.5 h-3.5 text-stone-400 transition-transform duration-200" :class="activeMegaMenu === 'occasions' ? 'rotate-180 text-rose-900' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>

                        <!-- Mega Dropdown: Occasion -->
                        <div
                            x-show="activeMegaMenu === 'occasions'"
                            x-cloak
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 translate-y-2"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 translate-y-2"
                            class="absolute left-0 top-full pt-2 w-[740px] z-50"
                        >
                            <div class="bg-white rounded-3xl shadow-2xl border border-stone-200 p-6 grid grid-cols-4 gap-4">
                                <!-- Occasion 1: Wedding & Bridal -->
                                <a href="{{ route('category.show', 'stilettos') }}" class="group/card p-3 rounded-2xl bg-stone-50 hover:bg-rose-50 border border-stone-200/70 transition flex flex-col justify-between">
                                    <div>
                                        <span class="text-2xl">💍</span>
                                        <h5 class="text-xs font-bold text-slate-900 group-hover/card:text-rose-950 font-serif mt-2">Wedding & Bridal</h5>
                                        <p class="text-[10px] text-stone-500 mt-1">Stilettos, pearl pumps & hand-embroidered resham heels.</p>
                                    </div>
                                    <span class="text-[10px] font-bold text-rose-800 mt-3 flex items-center gap-1">Explore →</span>
                                </a>

                                <!-- Occasion 2: Party & Festive -->
                                <a href="{{ route('category.show', 'block-heels') }}" class="group/card p-3 rounded-2xl bg-stone-50 hover:bg-rose-50 border border-stone-200/70 transition flex flex-col justify-between">
                                    <div>
                                        <span class="text-2xl">✨</span>
                                        <h5 class="text-xs font-bold text-slate-900 group-hover/card:text-rose-950 font-serif mt-2">Party & Festive</h5>
                                        <p class="text-[10px] text-stone-500 mt-1">Velvet block heels and metallic evening mules.</p>
                                    </div>
                                    <span class="text-[10px] font-bold text-rose-800 mt-3 flex items-center gap-1">Explore →</span>
                                </a>

                                <!-- Occasion 3: Office & Work -->
                                <a href="{{ route('category.show', 'flats') }}" class="group/card p-3 rounded-2xl bg-stone-50 hover:bg-rose-50 border border-stone-200/70 transition flex flex-col justify-between">
                                    <div>
                                        <span class="text-2xl">💼</span>
                                        <h5 class="text-xs font-bold text-slate-900 group-hover/card:text-rose-950 font-serif mt-2">Office & 9-to-5</h5>
                                        <p class="text-[10px] text-stone-500 mt-1">Pointed flats, kitten slingbacks & tailored loafers.</p>
                                    </div>
                                    <span class="text-[10px] font-bold text-rose-800 mt-3 flex items-center gap-1">Explore →</span>
                                </a>

                                <!-- Occasion 4: Casual & Daily -->
                                <a href="{{ route('category.show', 'boston-clogs') }}" class="group/card p-3 rounded-2xl bg-stone-50 hover:bg-rose-50 border border-stone-200/70 transition flex flex-col justify-between">
                                    <div>
                                        <span class="text-2xl">🌿</span>
                                        <h5 class="text-xs font-bold text-slate-900 group-hover/card:text-rose-950 font-serif mt-2">Casual Comfort</h5>
                                        <p class="text-[10px] text-stone-500 mt-1">Boston clogs, braided slides & ethnic daily chappals.</p>
                                    </div>
                                    <span class="text-[10px] font-bold text-rose-800 mt-3 flex items-center gap-1">Explore →</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- MEGA MENU ITEM 3: SHOP BY HEEL TYPE -->
                    <div class="relative" @mouseenter="openMega('heels')" @mouseleave="closeMega()">
                        <button
                            type="button"
                            class="px-3 py-1.5 rounded-lg hover:text-rose-950 hover:bg-stone-100 flex items-center gap-1 transition font-semibold cursor-pointer"
                            :class="activeMegaMenu === 'heels' ? 'text-rose-950 bg-stone-100' : ''"
                        >
                            <span>Heel Type</span>
                            <svg class="w-3.5 h-3.5 text-stone-400 transition-transform duration-200" :class="activeMegaMenu === 'heels' ? 'rotate-180 text-rose-900' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>

                        <!-- Mega Dropdown: Heel Type -->
                        <div
                            x-show="activeMegaMenu === 'heels'"
                            x-cloak
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 translate-y-2"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 translate-y-2"
                            class="absolute left-0 top-full pt-2 w-[620px] z-50"
                        >
                            <div class="bg-white rounded-3xl shadow-2xl border border-stone-200 p-6 grid grid-cols-2 gap-4">
                                <a href="{{ route('category.show', 'flats') }}" class="p-3.5 rounded-2xl bg-stone-50 hover:bg-rose-50 border border-stone-200/80 transition flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-white shadow-xs flex items-center justify-center text-xl shrink-0">🩴</div>
                                    <div>
                                        <h5 class="text-xs font-bold text-slate-900 font-serif">Flats & Low (0.5")</h5>
                                        <p class="text-[10px] text-stone-500">Ergonomic zero-strain daily walking support</p>
                                    </div>
                                </a>

                                <a href="{{ route('category.show', 'kitten-heels') }}" class="p-3.5 rounded-2xl bg-stone-50 hover:bg-rose-50 border border-stone-200/80 transition flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-white shadow-xs flex items-center justify-center text-xl shrink-0">🩰</div>
                                    <div>
                                        <h5 class="text-xs font-bold text-slate-900 font-serif">Kitten Heels (1.5"–2")</h5>
                                        <p class="text-[10px] text-stone-500">Subtle posture lift with all-day poise</p>
                                    </div>
                                </a>

                                <a href="{{ route('category.show', 'block-heels') }}" class="p-3.5 rounded-2xl bg-stone-50 hover:bg-rose-50 border border-stone-200/80 transition flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-white shadow-xs flex items-center justify-center text-xl shrink-0">👡</div>
                                    <div>
                                        <h5 class="text-xs font-bold text-slate-900 font-serif">Block Heels (2"–3")</h5>
                                        <p class="text-[10px] text-stone-500">Broad base stability for 6-hour weddings</p>
                                    </div>
                                </a>

                                <a href="{{ route('category.show', 'stilettos') }}" class="p-3.5 rounded-2xl bg-stone-50 hover:bg-rose-50 border border-stone-200/80 transition flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-white shadow-xs flex items-center justify-center text-xl shrink-0">👠</div>
                                    <div>
                                        <h5 class="text-xs font-bold text-slate-900 font-serif">Stiletto Heels (3.5")</h5>
                                        <p class="text-[10px] text-stone-500">Dramatic silhouette for gala receptions</p>
                                    </div>
                                </a>

                                <a href="{{ route('category.show', 'wedges') }}" class="p-3.5 rounded-2xl bg-stone-50 hover:bg-rose-50 border border-stone-200/80 transition flex items-center gap-3 col-span-2">
                                    <div class="w-10 h-10 rounded-xl bg-white shadow-xs flex items-center justify-center text-xl shrink-0">🪵</div>
                                    <div>
                                        <h5 class="text-xs font-bold text-slate-900 font-serif">Wedges & Flatforms (2.5")</h5>
                                        <p class="text-[10px] text-stone-500">Even weight distribution with breathable jute & cork soles</p>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Header Badges -->
                <div class="flex items-center gap-3 text-xs font-semibold">
                    <a href="#new-arrivals" class="text-rose-900 hover:text-rose-700 flex items-center gap-1 transition">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span>
                        <span>New In</span>
                    </a>
                    <span class="text-stone-300">•</span>
                    <a href="#best-selling" class="text-amber-800 hover:text-amber-950 flex items-center gap-1 transition">
                        <span>Best Sellers</span>
                    </a>
                    <span class="text-stone-300">•</span>
                    <a href="#outlet-section" class="inline-flex items-center gap-1 text-rose-800 bg-rose-50 px-2.5 py-0.5 rounded-full hover:bg-rose-100 transition font-bold text-[11px]">
                        <span>Outlet & Sale</span>
                    </a>
                </div>
            </nav>
        </div>
    </header>

    <!-- 4. MOBILE SLIDE-OUT DRAWER (Off-Canvas Menu with Categories, Occasion, & Heel Types) -->
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
            class="fixed inset-y-0 left-0 max-w-sm w-full bg-white shadow-2xl flex flex-col z-50 overflow-y-auto"
            x-show="mobileMenuOpen"
            x-transition:enter="transform transition ease-out duration-300"
            x-transition:enter-start="-translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transform transition ease-in duration-200"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="-translate-x-full"
        >
            <!-- Drawer Top Header -->
            <div class="p-4 border-b border-stone-800 flex items-center justify-between bg-stone-950 text-white">
                <a href="{{ route('home') }}" @click="mobileMenuOpen = false" class="flex items-center" aria-label="Heel Bari">
                    <img
                        src="{{ asset('images/logo/heel-bari-logo.webp') }}"
                        alt="Heel Bari"
                        class="h-8 w-auto object-contain"
                    >
                </a>
                <button
                    type="button"
                    class="p-2 text-stone-400 hover:text-white rounded-lg cursor-pointer"
                    @click="mobileMenuOpen = false"
                    aria-label="Close menu"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Drawer Navigation Tabs -->
            <div class="grid grid-cols-3 border-b border-stone-200 bg-stone-100 text-xs font-bold text-center">
                <button
                    type="button"
                    @click="mobileMenuTab = 'categories'"
                    class="py-3 transition border-b-2"
                    :class="mobileMenuTab === 'categories' ? 'border-rose-900 bg-white text-rose-950' : 'border-transparent text-stone-500'"
                >Categories</button>
                <button
                    type="button"
                    @click="mobileMenuTab = 'occasions'"
                    class="py-3 transition border-b-2"
                    :class="mobileMenuTab === 'occasions' ? 'border-rose-900 bg-white text-rose-950' : 'border-transparent text-stone-500'"
                >Occasions</button>
                <button
                    type="button"
                    @click="mobileMenuTab = 'heels'"
                    class="py-3 transition border-b-2"
                    :class="mobileMenuTab === 'heels' ? 'border-rose-900 bg-white text-rose-950' : 'border-transparent text-stone-500'"
                >Heel Type</button>
            </div>

            <!-- Tab 1: Categories List -->
            <div x-show="mobileMenuTab === 'categories'" class="p-4 space-y-1 flex-1">
                @foreach(($categories ?? collect()) as $cat)
                    <div x-data="{ expanded: false }" class="border-b border-stone-100 last:border-b-0 pb-1">
                        <div class="flex items-center justify-between min-h-[48px]">
                            <a
                                href="{{ route('category.show', $cat->slug) }}"
                                class="flex-1 py-3 px-3 rounded-xl text-sm font-semibold text-slate-800 hover:text-rose-950 hover:bg-stone-50 flex items-center justify-between"
                                @click="mobileMenuOpen = false"
                            >
                                <span>{{ $cat->name }}</span>
                                <span class="text-xs text-stone-400 font-normal">({{ $cat->products_count }})</span>
                            </a>
                            @if($cat->children->isNotEmpty())
                                <button
                                    type="button"
                                    class="p-3 text-stone-400 hover:text-slate-800"
                                    @click="expanded = !expanded"
                                    aria-label="Toggle subcategories"
                                >
                                    <svg class="w-4 h-4 transition-transform duration-200" :class="expanded ? 'rotate-180 text-rose-900' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                            @endif
                        </div>

                        <!-- Subcategory child links -->
                        @if($cat->children->isNotEmpty())
                            <div x-show="expanded" x-collapse class="pl-4 pr-2 py-1 space-y-1 bg-stone-50/70 rounded-xl mb-2">
                                @foreach($cat->children as $child)
                                    <a
                                        href="{{ route('category.show', $child->slug) }}"
                                        class="block py-2.5 px-3 rounded-lg text-xs text-stone-600 hover:text-rose-950 hover:bg-white font-medium transition"
                                        @click="mobileMenuOpen = false"
                                    >
                                        {{ $child->name }}
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <!-- Tab 2: Occasions -->
            <div x-show="mobileMenuTab === 'occasions'" class="p-4 space-y-2 flex-1">
                <a href="{{ route('category.show', 'stilettos') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 p-3.5 rounded-2xl bg-stone-50 hover:bg-rose-50 border border-stone-200/80 transition">
                    <span class="text-2xl">💍</span>
                    <div>
                        <h4 class="text-xs font-bold text-slate-900 font-serif">Wedding & Reception</h4>
                        <p class="text-[10px] text-stone-500">Stilettos, pearls, resham zari embroidery</p>
                    </div>
                </a>
                <a href="{{ route('category.show', 'block-heels') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 p-3.5 rounded-2xl bg-stone-50 hover:bg-rose-50 border border-stone-200/80 transition">
                    <span class="text-2xl">✨</span>
                    <div>
                        <h4 class="text-xs font-bold text-slate-900 font-serif">Party & Festive</h4>
                        <p class="text-[10px] text-stone-500">Velvet block heels, metallic mules</p>
                    </div>
                </a>
                <a href="{{ route('category.show', 'flats') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 p-3.5 rounded-2xl bg-stone-50 hover:bg-rose-50 border border-stone-200/80 transition">
                    <span class="text-2xl">💼</span>
                    <div>
                        <h4 class="text-xs font-bold text-slate-900 font-serif">Work & 9-to-5</h4>
                        <p class="text-[10px] text-stone-500">Pointed flats, kitten slingbacks, loafers</p>
                    </div>
                </a>
                <a href="{{ route('category.show', 'boston-clogs') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 p-3.5 rounded-2xl bg-stone-50 hover:bg-rose-50 border border-stone-200/80 transition">
                    <span class="text-2xl">🌿</span>
                    <div>
                        <h4 class="text-xs font-bold text-slate-900 font-serif">Casual & Daily</h4>
                        <p class="text-[10px] text-stone-500">Boston clogs, soft slides & daily chappals</p>
                    </div>
                </a>
            </div>

            <!-- Tab 3: Heel Type -->
            <div x-show="mobileMenuTab === 'heels'" class="p-4 space-y-2 flex-1">
                <a href="{{ route('category.show', 'flats') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 p-3 rounded-2xl bg-stone-50 border border-stone-200/80">
                    <span class="text-xl">🩴</span>
                    <span class="text-xs font-bold text-slate-900 font-serif">Flat & Low Sole (0.5")</span>
                </a>
                <a href="{{ route('category.show', 'kitten-heels') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 p-3 rounded-2xl bg-stone-50 border border-stone-200/80">
                    <span class="text-xl">🩰</span>
                    <span class="text-xs font-bold text-slate-900 font-serif">Kitten Heels (1.5"–2")</span>
                </a>
                <a href="{{ route('category.show', 'block-heels') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 p-3 rounded-2xl bg-stone-50 border border-stone-200/80">
                    <span class="text-xl">👡</span>
                    <span class="text-xs font-bold text-slate-900 font-serif">Block Heels (2"–3")</span>
                </a>
                <a href="{{ route('category.show', 'stilettos') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 p-3 rounded-2xl bg-stone-50 border border-stone-200/80">
                    <span class="text-xl">👠</span>
                    <span class="text-xs font-bold text-slate-900 font-serif">Stiletto Heels (3.5")</span>
                </a>
                <a href="{{ route('category.show', 'wedges') }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 p-3 rounded-2xl bg-stone-50 border border-stone-200/80">
                    <span class="text-xl">🪵</span>
                    <span class="text-xs font-bold text-slate-900 font-serif">Wedges & Platforms (2.5")</span>
                </a>
            </div>

            <!-- Drawer Bottom Direct Action Links -->
            <div class="p-4 border-t border-stone-200 bg-stone-50 space-y-2 text-xs">
                <a href="{{ route('track-order') }}" @click="mobileMenuOpen = false" class="flex items-center gap-2 py-2 px-3 rounded-xl bg-white border border-stone-200 text-slate-800 font-semibold">
                    <span>🚚</span>
                    <span>Track Consignment / Order</span>
                </a>
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $storeSettings['whatsapp_number'] ?? '+8801700000000') }}" target="_blank" rel="noopener" class="flex items-center gap-2 py-2 px-3 rounded-xl bg-emerald-600 text-white font-bold">
                    <span>💬</span>
                    <span>WhatsApp Order & Advice</span>
                </a>
                <div class="pt-2 text-center text-[10px] text-stone-400">
                    Hotline: {{ $storeSettings['support_phone'] ?? '+880 1700-000000' }}
                </div>
            </div>
        </div>
    </div>

    <!-- 5. WISHLIST SLIDE-OVER DRAWER -->
    <div
        class="relative z-50"
        x-show="wishlistDrawerOpen"
        x-cloak
        aria-modal="true"
    >
        <div
            class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs transition-opacity"
            x-show="wishlistDrawerOpen"
            x-transition.opacity
            @click="wishlistDrawerOpen = false"
        ></div>

        <div
            class="fixed inset-y-0 right-0 max-w-sm w-full bg-white shadow-2xl flex flex-col z-50"
            x-show="wishlistDrawerOpen"
            x-transition:enter="transform transition ease-out duration-300"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transform transition ease-in duration-200"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
        >
            <div class="p-4 border-b border-stone-200 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="text-rose-800 text-xl">♥</span>
                    <h3 class="text-sm font-bold text-slate-900 font-serif">Saved Wishlist (<span x-text="wishlistCount"></span>)</h3>
                </div>
                <button type="button" @click="wishlistDrawerOpen = false" class="p-2 text-stone-400 hover:text-slate-800 rounded-lg">✕</button>
            </div>

            <div class="p-4 flex-1 overflow-y-auto">
                <template x-if="wishlistCount === 0">
                    <div class="py-16 text-center text-stone-500 space-y-3">
                        <span class="text-4xl block">🤍</span>
                        <p class="text-sm font-bold text-slate-800">Your wishlist is empty</p>
                        <p class="text-xs text-stone-400 max-w-xs mx-auto">Tap the heart icon on any product to save your dream pairs for later.</p>
                        <a href="{{ route('products.index') }}" @click="wishlistDrawerOpen = false" class="inline-block mt-4 px-6 py-2.5 rounded-full bg-rose-950 text-white text-xs font-bold">
                            Explore Catalog
                        </a>
                    </div>
                </template>

                <template x-if="wishlistCount > 0">
                    <div class="space-y-3">
                        <p class="text-xs text-stone-500">You have <strong x-text="wishlistCount"></strong> pair(s) saved in your local wishlist:</p>
                        <div class="p-3 rounded-2xl bg-rose-50 border border-rose-200/70 text-xs text-rose-950">
                            💡 Saved items are remembered on your device. Ready to order? Click below to view catalog.
                        </div>
                        <a
                            href="{{ route('products.index') }}"
                            @click="wishlistDrawerOpen = false"
                            class="block w-full text-center py-3 rounded-2xl bg-rose-950 text-white text-xs font-bold uppercase tracking-wider hover:bg-rose-900 transition"
                        >
                            View All Shoes & Add to Bag
                        </a>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- 6. SEARCH MODAL OVERLAY -->
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
            class="relative mx-auto max-w-2xl transform rounded-3xl bg-white p-4 sm:p-6 shadow-2xl transition-all border border-stone-200"
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
                    placeholder="Search by shoe name or category (e.g. block heels, flats, mules)..."
                    class="h-12 w-full rounded-2xl bg-stone-50 pl-11 pr-10 text-sm text-slate-900 placeholder:text-stone-400 focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-rose-800/40 border border-stone-200"
                >
                <button
                    type="button"
                    class="absolute right-3 text-stone-400 hover:text-slate-700"
                    @click="searchModalOpen = false"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Quick Suggestions -->
            <div class="mt-3 flex items-center gap-2 text-xs text-stone-500 overflow-x-auto no-scrollbar py-1">
                <span class="font-semibold text-stone-400">Popular:</span>
                <button type="button" @click="searchQuery = 'Velvet'; performSearch()" class="px-2.5 py-1 rounded-full bg-stone-100 hover:bg-rose-50 hover:text-rose-900 transition">Velvet Block Heels</button>
                <button type="button" @click="searchQuery = 'Boston'; performSearch()" class="px-2.5 py-1 rounded-full bg-stone-100 hover:bg-rose-50 hover:text-rose-900 transition">Boston Clogs</button>
                <button type="button" @click="searchQuery = 'Stiletto'; performSearch()" class="px-2.5 py-1 rounded-full bg-stone-100 hover:bg-rose-50 hover:text-rose-900 transition">Stilettos</button>
                <button type="button" @click="searchQuery = 'Kolhapuri'; performSearch()" class="px-2.5 py-1 rounded-full bg-stone-100 hover:bg-rose-50 hover:text-rose-900 transition">Kolhapuri</button>
            </div>

            <!-- Search Loading -->
            <div x-show="isSearching" class="py-8 text-center text-sm text-stone-500">
                <span class="inline-block animate-spin mr-2">⏳</span> Searching catalog...
            </div>

            <!-- Search Results -->
            <div x-show="!isSearching && searchResults.length > 0" class="mt-4 divide-y divide-stone-100 max-h-96 overflow-y-auto">
                <template x-for="item in searchResults" :key="item.id">
                    <a
                        :href="'/product/' + item.slug"
                        @click="searchModalOpen = false"
                        class="flex items-center gap-4 py-3 px-2 rounded-2xl hover:bg-rose-50/60 transition group"
                    >
                        <img :src="item.image_url" :alt="item.name" class="w-14 h-14 rounded-xl object-cover bg-stone-100 border border-stone-200">
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

            <!-- Empty Results -->
            <div x-show="!isSearching && searchResults.length === 0 && searchQuery.trim().length >= 2" class="py-8 text-center text-sm text-stone-500">
                No pairs found matching "<strong x-text="searchQuery"></strong>". Try searching for "Heels", "Flats", or "Slides".
            </div>
        </div>
    </div>

    <!-- 7. MAIN STOREFRONT CONTENT -->
    <main class="flex-1">
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    <!-- 8. FLOATING TOAST NOTIFICATION -->
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

    <!-- 9. FLOATING WHATSAPP BUTTON (Quick Order / Consultation) -->
    <aside class="fixed bottom-20 md:bottom-8 right-4 md:right-8 z-40">
        <a
            href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $storeSettings['whatsapp_number'] ?? '+8801700000000') }}?text={{ urlencode('Hi Heel Bari! I would like assistance with choosing my footwear size / ordering.') }}"
            target="_blank"
            rel="noopener"
            class="flex items-center gap-2 px-4 py-3 rounded-full bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-2xl hover:shadow-emerald-600/50 transition-all transform hover:scale-105 active:scale-95 group"
            title="Chat with Shoe Specialist on WhatsApp"
            aria-label="Contact Heel Bari on WhatsApp"
        >
            <span class="w-2.5 h-2.5 rounded-full bg-white animate-ping"></span>
            <span class="text-base">💬</span>
            <span class="hidden sm:inline font-semibold">WhatsApp Order</span>
        </a>
    </aside>

    <!-- 10. MOBILE BOTTOM NAVIGATION BAR (Home, Categories, Cart, Account) -->
    <nav class="md:hidden fixed bottom-0 inset-x-0 z-40 bg-white/98 backdrop-blur-md border-t border-stone-200 shadow-xl pb-[max(env(safe-area-inset-bottom),0.5rem)]">
        <div class="grid grid-cols-4 h-16 text-[11px] font-semibold text-stone-600">
            <!-- Tab 1: Home -->
            <a
                href="{{ route('home') }}"
                class="flex flex-col items-center justify-center gap-1 min-h-[48px] {{ request()->routeIs('home') ? 'text-rose-950 font-bold' : 'hover:text-rose-900' }}"
                aria-label="Home"
            >
                <svg class="w-5 h-5 {{ request()->routeIs('home') ? 'text-rose-900 stroke-[2.2]' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span>Home</span>
            </a>

            <!-- Tab 2: Categories (Smooth Drawer) -->
            <button
                type="button"
                @click="mobileMenuOpen = true"
                class="flex flex-col items-center justify-center gap-1 min-h-[48px] hover:text-rose-950 cursor-pointer"
                aria-label="Browse Footwear Categories"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16M4 12h16M4 18h7"/>
                </svg>
                <span>Categories</span>
            </button>

            <!-- Tab 3: Cart (Live Count Badge) -->
            <button
                type="button"
                @click="$dispatch('open-mini-cart')"
                class="relative flex flex-col items-center justify-center gap-1 min-h-[48px] hover:text-rose-950 cursor-pointer"
                aria-label="Shopping Bag"
            >
                <div class="relative">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                    <span
                        class="absolute -top-1.5 -right-2 bg-rose-800 text-white text-[9px] font-bold h-4 w-4 rounded-full flex items-center justify-center"
                        x-text="cartCount"
                        x-show="cartCount > 0"
                    >{{ $cartCount ?? 0 }}</span>
                </div>
                <span>Bag</span>
            </button>

            <!-- Tab 4: Account -->
            <a
                href="{{ Auth::check() ? route('account.dashboard') : route('login') }}"
                class="flex flex-col items-center justify-center gap-1 min-h-[48px] {{ request()->routeIs('account.*') || request()->routeIs('login') ? 'text-rose-950 font-bold' : 'hover:text-rose-900' }}"
                aria-label="Customer Account"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                <span>Account</span>
            </a>
        </div>
    </nav>

    <!-- 11. LUXURY STOREFRONT FOOTER -->
    <footer class="bg-stone-900 text-stone-300 pt-16 pb-28 md:pb-12 border-t border-stone-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10">

                <!-- Col 1: Brand Story & Values -->
                <div class="space-y-4">
                    <a href="{{ route('home') }}" class="inline-block" aria-label="Heel Bari">
                        <img
                            src="{{ asset('images/logo/heel-bari-logo.webp') }}"
                            alt="Heel Bari"
                            class="h-10 sm:h-12 w-auto object-contain"
                        >
                    </a>
                    <p class="text-xs sm:text-sm text-stone-400 leading-relaxed">
                        Handcrafted luxury footwear designed specifically for Bangladeshi women. Celebrating master shoemaking craftsmanship with orthopedic memory foam cushions for 6-hour celebratory wear.
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
                    <ul class="space-y-2.5 text-xs sm:text-sm text-stone-400">
                        <li><a href="{{ route('category.show', 'block-heels') }}" class="hover:text-white hover:translate-x-1 inline-block transition">Block Heels</a></li>
                        <li><a href="{{ route('category.show', 'kitten-heels') }}" class="hover:text-white hover:translate-x-1 inline-block transition">Kitten Heels</a></li>
                        <li><a href="{{ route('category.show', 'stilettos') }}" class="hover:text-white hover:translate-x-1 inline-block transition">Bridal Stilettos</a></li>
                        <li><a href="{{ route('category.show', 'flats') }}" class="hover:text-white hover:translate-x-1 inline-block transition">Pointed & Ballet Flats</a></li>
                        <li><a href="{{ route('category.show', 'boston-clogs') }}" class="hover:text-white hover:translate-x-1 inline-block transition">Suede Boston Clogs</a></li>
                        <li><a href="{{ route('category.show', 'chappal') }}" class="hover:text-white hover:translate-x-1 inline-block transition">Ethnic Kolhapuri Chappals</a></li>
                        <li><a href="#outlet-section" class="text-rose-400 font-semibold hover:text-rose-300">Outlet & Clearance Pairs →</a></li>
                    </ul>
                </div>

                <!-- Col 3: Customer Care & Policies -->
                <div>
                    <h3 class="text-xs font-bold text-white uppercase tracking-widest mb-4">Customer Care & Policies</h3>
                    <ul class="space-y-2.5 text-xs sm:text-sm text-stone-400">
                        <li><a href="{{ route('track-order') }}" class="hover:text-white transition">Track Your Consignment</a></li>
                        <li><a href="#trust-strip" class="hover:text-white transition">7 Days Doorstep Size Exchange</a></li>
                        <li><a href="#trust-strip" class="hover:text-white transition">Delivery Charges (Dhaka ৳70, BD ৳130)</a></li>
                        <li><a href="#outlets" class="hover:text-white transition">Boutique Locations in Dhaka & CTG</a></li>
                        <li><a href="/admin" class="hover:text-white transition">Staff Management Portal</a></li>
                    </ul>
                </div>

                <!-- Col 4: Retail Boutiques & Social Links -->
                <div class="space-y-4">
                    <h3 class="text-xs font-bold text-white uppercase tracking-widest mb-4">Retail Boutiques & Social</h3>
                    <p class="text-xs text-stone-400">Visit our boutiques in Dhanmondi, Banani, Uttara, Bashundhara City, Jamuna Future Park & Chattogram GEC.</p>

                    <!-- Social Media Links -->
                    <div class="flex items-center gap-3">
                        <a href="{{ $storeSettings['facebook_url'] ?? 'https://facebook.com' }}" target="_blank" rel="noopener" class="w-10 h-10 rounded-full bg-stone-800 hover:bg-rose-950 text-white flex items-center justify-center transition shadow-xs" aria-label="Heel Bari Facebook">
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </a>
                        <a href="{{ $storeSettings['instagram_url'] ?? 'https://instagram.com' }}" target="_blank" rel="noopener" class="w-10 h-10 rounded-full bg-stone-800 hover:bg-rose-950 text-white flex items-center justify-center transition shadow-xs" aria-label="Heel Bari Instagram">
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                        </a>
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $storeSettings['whatsapp_number'] ?? '+8801700000000') }}" target="_blank" rel="noopener" class="w-10 h-10 rounded-full bg-emerald-600 hover:bg-emerald-500 text-white flex items-center justify-center transition shadow-xs" aria-label="WhatsApp Order">
                            <span class="text-base">💬</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Bottom Bar: Accepted Payment Methods & Copyright -->
            <div class="mt-12 pt-8 border-t border-stone-800 flex flex-col md:flex-row items-center justify-between gap-6 text-xs text-stone-500">
                <div class="flex items-center gap-2 text-center md:text-left">
                    <span>© {{ date('Y') }} {{ $storeSettings['shop_name'] ?? 'Heel Bari' }} Ltd. All rights reserved. Made with ❤️ in Dhaka, Bangladesh.</span>
                </div>

                <!-- Accepted Payment Badges (Authentic Local & Global) -->
                <div class="flex items-center flex-wrap justify-center gap-2">
                    <span class="text-[11px] text-stone-400 font-semibold mr-1">Payment Gateways:</span>
                    <span class="px-2.5 py-1 rounded-lg bg-stone-800 text-rose-300 font-bold text-[11px] border border-stone-700">bKash</span>
                    <span class="px-2.5 py-1 rounded-lg bg-stone-800 text-orange-400 font-bold text-[11px] border border-stone-700">Nagad</span>
                    <span class="px-2.5 py-1 rounded-lg bg-stone-800 text-purple-300 font-bold text-[11px] border border-stone-700">Rocket</span>
                    <span class="px-2.5 py-1 rounded-lg bg-stone-800 text-blue-300 font-bold text-[11px] border border-stone-700">VISA</span>
                    <span class="px-2.5 py-1 rounded-lg bg-stone-800 text-amber-300 font-bold text-[11px] border border-stone-700">Mastercard</span>
                    <span class="px-2.5 py-1 rounded-lg bg-stone-800 text-emerald-400 font-bold text-[11px] border border-stone-700">Cash on Delivery</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- 12. MINI-CART DRAWER -->
    <livewire:cart.mini-cart />

    @livewireScripts
</body>
</html>
