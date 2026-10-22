@extends('layouts.storefront')

@section('title', ($storeSettings['shop_name'] ?? '[BRAND_NAME]') . ' | Handcrafted Women\'s Footwear Bangladesh')

@section('content')

    <!-- 1. HERO BANNER SLIDER (Admin-Managed via Filament Hero Banners) -->
    <section
        id="hero-slider"
        class="relative overflow-hidden bg-stone-950 text-white"
        x-data="{
            activeSlide: 0,
            slidesCount: {{ $banners->count() }},
            autoPlayInterval: null,
            isPaused: false,

            init() {
                this.startAutoPlay();
            },
            startAutoPlay() {
                if (this.slidesCount <= 1) return;
                this.autoPlayInterval = setInterval(() => {
                    if (!this.isPaused) {
                        this.nextSlide();
                    }
                }, 5500);
            },
            stopAutoPlay() {
                if (this.autoPlayInterval) clearInterval(this.autoPlayInterval);
            },
            nextSlide() {
                this.activeSlide = (this.activeSlide + 1) % this.slidesCount;
            },
            prevSlide() {
                this.activeSlide = (this.activeSlide - 1 + this.slidesCount) % this.slidesCount;
            },
            goToSlide(idx) {
                this.activeSlide = idx;
            }
        }"
        @mouseenter="isPaused = true"
        @mouseleave="isPaused = false"
    >
        <!-- Slides Container -->
        <div class="relative min-h-[520px] sm:min-h-[580px] lg:min-h-[640px] flex items-center">
            @foreach($banners as $index => $banner)
                <div
                    class="absolute inset-0 transition-opacity duration-700 ease-in-out flex items-center bg-gradient-to-r {{ $banner->bg_gradient ?? 'from-rose-950 via-neutral-900 to-black' }}"
                    x-show="activeSlide === {{ $index }}"
                    x-transition:enter="opacity-0 scale-98"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-98"
                    x-cloak
                >
                    <!-- Background ambient lighting effect -->
                    <div class="absolute -right-20 -top-20 w-96 h-96 rounded-full bg-rose-600/15 blur-3xl pointer-events-none"></div>
                    <div class="absolute right-1/3 -bottom-20 w-80 h-80 rounded-full bg-amber-500/10 blur-3xl pointer-events-none"></div>

                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 w-full relative z-10">
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">

                            <!-- Left Text Content -->
                            <div class="lg:col-span-7 space-y-4 sm:space-y-6 text-center lg:text-left">
                                @if($banner->badge)
                                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-rose-500/20 text-rose-300 border border-rose-400/30 text-xs font-bold tracking-widest uppercase">
                                        <span class="w-2 h-2 rounded-full bg-rose-400 animate-ping"></span>
                                        <span>{{ $banner->badge }}</span>
                                    </div>
                                @endif

                                <h1 class="text-3xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-white leading-tight font-serif">
                                    {{ $banner->title }}
                                </h1>

                                @if($banner->subtitle)
                                    <p class="text-base sm:text-lg text-stone-300 max-w-xl mx-auto lg:mx-0 font-normal leading-relaxed">
                                        {{ $banner->subtitle }}
                                    </p>
                                @endif

                                <div class="pt-2 flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4">
                                    <a
                                        href="{{ $banner->button_link ?? '#new-arrivals' }}"
                                        class="w-full sm:w-auto px-8 py-4 rounded-full bg-rose-800 hover:bg-rose-700 text-white font-bold text-sm tracking-wider uppercase shadow-xl hover:shadow-rose-900/50 transition-all transform hover:-translate-y-0.5 text-center flex items-center justify-center gap-2"
                                    >
                                        <span>{{ $banner->button_text ?? 'Shop Collection' }}</span>
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                    </a>

                                    <a
                                        href="#categories"
                                        class="w-full sm:w-auto px-7 py-4 rounded-full bg-white/10 hover:bg-white/20 text-stone-200 font-semibold text-sm border border-white/20 backdrop-blur-xs transition text-center"
                                    >
                                        Browse All Categories
                                    </a>
                                </div>

                                <!-- Trust Micro Highlights -->
                                <div class="pt-4 flex items-center justify-center lg:justify-start gap-6 text-xs text-stone-400">
                                    <span class="flex items-center gap-1.5">
                                        <span class="text-amber-400">★</span> 4.9/5 Rating
                                    </span>
                                    <span class="flex items-center gap-1.5">
                                        <span class="text-emerald-400">✓</span> Cash on Delivery
                                    </span>
                                    <span class="flex items-center gap-1.5">
                                        <span class="text-blue-400">🚚</span> 24h Dhaka Dispatch
                                    </span>
                                </div>
                            </div>

                            <!-- Right Visual / Shoe Showcase -->
                            <div class="lg:col-span-5 flex justify-center relative">
                                <div class="relative w-72 sm:w-96 lg:w-full max-w-md aspect-square rounded-3xl overflow-hidden shadow-2xl border border-white/10 group">
                                    <img
                                        src="{{ $banner->image_url }}"
                                        alt="{{ $banner->title }}"
                                        width="600"
                                        height="600"
                                        {{ $index === 0 ? 'fetchpriority=high' : 'loading=lazy' }}
                                        class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-700"
                                    >
                                    <!-- Floating Price/Craft Tag -->
                                    <div class="absolute bottom-4 left-4 right-4 bg-slate-900/90 backdrop-blur-md p-3.5 rounded-2xl border border-white/10 flex items-center justify-between">
                                        <div class="flex items-center gap-2.5">
                                            <span class="text-xl">✨</span>
                                            <div>
                                                <p class="text-xs font-bold text-white">Cloud Cushion Foam</p>
                                                <p class="text-[10px] text-stone-400">Engineered for pain-free wear</p>
                                            </div>
                                        </div>
                                        <span class="text-xs font-bold text-rose-300 bg-rose-950/80 px-2.5 py-1 rounded-full border border-rose-800">
                                            100% BD Handcrafted
                                        </span>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Slider Controls (Arrows & Pagination Dots) -->
        @if($banners->count() > 1)
            <!-- Prev Arrow -->
            <button
                type="button"
                class="hidden sm:flex absolute left-4 top-1/2 -translate-y-1/2 z-20 w-11 h-11 rounded-full bg-white/10 hover:bg-white/25 text-white backdrop-blur-md items-center justify-center border border-white/20 transition"
                @click="prevSlide()"
                aria-label="Previous Slide"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </button>

            <!-- Next Arrow -->
            <button
                type="button"
                class="hidden sm:flex absolute right-4 top-1/2 -translate-y-1/2 z-20 w-11 h-11 rounded-full bg-white/10 hover:bg-white/25 text-white backdrop-blur-md items-center justify-center border border-white/20 transition"
                @click="nextSlide()"
                aria-label="Next Slide"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>

            <!-- Pagination Dots -->
            <div class="absolute bottom-5 inset-x-0 z-20 flex justify-center items-center gap-2">
                @foreach($banners as $index => $banner)
                    <button
                        type="button"
                        class="h-2 rounded-full transition-all duration-300"
                        :class="activeSlide === {{ $index }} ? 'w-8 bg-rose-500' : 'w-2.5 bg-white/40 hover:bg-white/70'"
                        @click="goToSlide({{ $index }})"
                        aria-label="Go to slide {{ $index + 1 }}"
                    ></button>
                @endforeach
            </div>
        @endif
    </section>

    <!-- 2. VALUE PROPOSITION & TRUST BADGES -->
    <section class="bg-white border-b border-stone-200 py-6 sm:py-8 shadow-2xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 sm:gap-8">
                <!-- Trust 1 -->
                <div class="flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-2xl bg-rose-50 text-rose-800 flex items-center justify-center text-xl shrink-0">
                        🚚
                    </div>
                    <div>
                        <h4 class="text-xs sm:text-sm font-bold text-slate-900">Free Home Delivery</h4>
                        <p class="text-[11px] sm:text-xs text-stone-500">Inside Dhaka on ৳{{ number_format($storeSettings['free_delivery_threshold'] ?? 3000) }}+</p>
                    </div>
                </div>

                <!-- Trust 2 -->
                <div class="flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-2xl bg-amber-50 text-amber-800 flex items-center justify-center text-xl shrink-0">
                        🔄
                    </div>
                    <div>
                        <h4 class="text-xs sm:text-sm font-bold text-slate-900">7 Days Size Exchange</h4>
                        <p class="text-[11px] sm:text-xs text-stone-500">Hassle-free doorstep swap</p>
                    </div>
                </div>

                <!-- Trust 3 -->
                <div class="flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-2xl bg-emerald-50 text-emerald-800 flex items-center justify-center text-xl shrink-0">
                        🇧🇩
                    </div>
                    <div>
                        <h4 class="text-xs sm:text-sm font-bold text-slate-900">100% Handcrafted</h4>
                        <p class="text-[11px] sm:text-xs text-stone-500">By master artisans in BD</p>
                    </div>
                </div>

                <!-- Trust 4 -->
                <div class="flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-2xl bg-blue-50 text-blue-800 flex items-center justify-center text-xl shrink-0">
                        💵
                    </div>
                    <div>
                        <h4 class="text-xs sm:text-sm font-bold text-slate-900">Cash on Delivery</h4>
                        <p class="text-[11px] sm:text-xs text-stone-500">bKash & COD in all 64 districts</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 3. CATEGORY GRID (Visual Cards with Shoes Count & Subcategories) -->
    <section id="categories" class="py-14 sm:py-20 bg-stone-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-10 gap-4">
                <div>
                    <span class="text-xs font-bold text-rose-900 uppercase tracking-widest">Handcrafted Collections</span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight font-serif mt-1">
                        Explore Footwear by Category
                    </h2>
                </div>
                <p class="text-xs sm:text-sm text-stone-500 max-w-sm">
                    From 6-hour festive block heels to everyday cloud-soft clogs, find your perfect silhouette.
                </p>
            </div>

            <!-- Categories Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
                @foreach($categories as $category)
                    <div
                        id="category-{{ $category->slug }}"
                        class="group relative bg-white rounded-2xl overflow-hidden shadow-xs hover:shadow-xl border border-stone-200/80 transition-all duration-300 flex flex-col"
                    >
                        <!-- Category Image Container -->
                        <div class="relative aspect-square overflow-hidden bg-stone-100">
                            <img
                                src="{{ $category->image_url }}"
                                alt="{{ $category->name }}"
                                width="600"
                                height="600"
                                loading="lazy"
                                class="w-full h-full object-cover group-hover:scale-108 transition-transform duration-500"
                            >
                            <!-- Product count badge -->
                            <div class="absolute top-3 right-3 bg-white/90 backdrop-blur-xs text-slate-800 text-[11px] font-bold px-2.5 py-1 rounded-full shadow-xs">
                                {{ $category->products_count }} pairs
                            </div>
                        </div>

                        <!-- Card Content -->
                        <div class="p-4 sm:p-5 flex-1 flex flex-col justify-between">
                            <div>
                                <h3 class="text-base sm:text-lg font-bold text-slate-900 group-hover:text-rose-900 transition-colors font-serif">
                                    {{ $category->name }}
                                </h3>
                                <p class="text-xs text-stone-500 mt-1 line-clamp-2">
                                    {{ $category->description ?? 'Handcrafted women footwear with cushioned footbeds.' }}
                                </p>
                            </div>

                            <!-- Subcategories Pills -->
                            @if($category->children->isNotEmpty())
                                <div class="mt-3 pt-3 border-t border-stone-100 flex flex-wrap gap-1.5">
                                    @foreach($category->children->take(2) as $child)
                                        <span class="text-[10px] bg-stone-100 text-stone-600 px-2 py-0.5 rounded-md font-medium">
                                            {{ $child->name }}
                                        </span>
                                    @endforeach
                                    @if($category->children->count() > 2)
                                        <span class="text-[10px] text-rose-800 font-semibold self-center">
                                            +{{ $category->children->count() - 2 }} more
                                        </span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- 4. NEW ARRIVALS (Product Cards with Sizes, Prices, & Quick Add) -->
    <section id="new-arrivals" class="py-14 sm:py-20 bg-white border-t border-stone-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-10 gap-4">
                <div>
                    <div class="inline-flex items-center gap-2 text-rose-800 text-xs font-bold uppercase tracking-widest mb-1">
                        <span class="w-2 h-2 rounded-full bg-rose-600 animate-pulse"></span>
                        <span>Just Dropped</span>
                    </div>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight font-serif">
                        New Festive and Everyday Arrivals
                    </h2>
                </div>
                <div class="text-xs sm:text-sm text-stone-500">
                    Showing latest handcrafted designs for 2026
                </div>
            </div>

            <!-- Product Grid -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                @foreach($newArrivals as $product)
                    <article
                        id="product-{{ $product->slug }}"
                        class="group bg-white rounded-2xl border border-stone-200 hover:border-rose-900/30 overflow-hidden shadow-xs hover:shadow-xl transition-all duration-300 flex flex-col justify-between"
                    >
                        <!-- Image Container with Badges -->
                        <div class="relative aspect-square overflow-hidden bg-stone-100">
                            <img
                                src="{{ $product->image_url }}"
                                alt="{{ $product->name }}"
                                width="600"
                                height="600"
                                loading="lazy"
                                class="w-full h-full object-cover group-hover:scale-106 transition-transform duration-500"
                            >

                            <!-- Discount / New Badge -->
                            <div class="absolute top-3 left-3 flex flex-col gap-1">
                                @if($product->has_discount)
                                    <span class="bg-rose-800 text-white text-[10px] font-extrabold px-2 py-0.5 rounded-full uppercase tracking-wider shadow-xs">
                                        -{{ $product->discount_percentage }}%
                                    </span>
                                @else
                                    <span class="bg-slate-900 text-white text-[10px] font-extrabold px-2 py-0.5 rounded-full uppercase tracking-wider shadow-xs">
                                        NEW
                                    </span>
                                @endif
                            </div>

                            <!-- Rating Overlay -->
                            <div class="absolute bottom-3 left-3 bg-white/90 backdrop-blur-xs text-slate-800 text-[10px] font-bold px-2 py-0.5 rounded-full shadow-xs flex items-center gap-1">
                                <span class="text-amber-500">★</span>
                                <span>{{ $product->average_rating }}</span>
                                <span class="text-stone-400 font-normal">({{ $product->reviews_count }})</span>
                            </div>
                        </div>

                        <!-- Product Info -->
                        <div class="p-4 flex-1 flex flex-col justify-between">
                            <div>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-rose-800">
                                    {{ $product->category?->name ?? 'Footwear' }}
                                </span>
                                <h3 class="text-sm font-bold text-slate-900 group-hover:text-rose-900 transition-colors line-clamp-1 mt-0.5">
                                    {{ $product->name }}
                                </h3>

                                <!-- Available Sizes -->
                                @if(!empty($product->available_sizes))
                                    <div class="mt-2 flex items-center gap-1 overflow-x-auto no-scrollbar py-0.5">
                                        <span class="text-[10px] text-stone-400 font-medium mr-1">EU:</span>
                                        @foreach(array_slice($product->available_sizes, 0, 5) as $size)
                                            <span class="text-[10px] bg-stone-100 text-stone-700 px-1.5 py-0.5 rounded font-mono font-medium">
                                                {{ $size }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <!-- Price & Quick Add Button -->
                            <div class="mt-4 pt-3 border-t border-stone-100 flex items-center justify-between">
                                <div>
                                    <div class="text-sm sm:text-base font-extrabold text-slate-900">
                                        ৳ {{ number_format($product->effective_price) }}
                                    </div>
                                    @if($product->has_discount)
                                        <div class="text-xs text-stone-400 line-through">
                                            ৳ {{ number_format($product->original_price) }}
                                        </div>
                                    @endif
                                </div>

                                <!-- Add to Bag Action -->
                                @php
                                    $firstVariant = $product->variants->where('stock', '>', 0)->first() ?? $product->variants->first();
                                @endphp
                                <button
                                    type="button"
                                    class="p-2 sm:px-3 sm:py-2 rounded-xl bg-slate-900 hover:bg-rose-900 text-white text-xs font-semibold flex items-center gap-1.5 transition-colors shadow-xs active:scale-95"
                                    @click="quickAdd({{ $firstVariant?->id ?? 0 }})"
                                    title="Add to Shopping Bag"
                                    aria-label="Add {{ $product->name }} to shopping bag"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    <span class="hidden sm:inline">Add</span>
                                </button>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <!-- 5. FESTIVE SPOTLIGHT BANNER (Craftsmanship & Dhaka Pride) -->
    <section class="py-12 bg-gradient-to-r from-rose-950 via-neutral-900 to-stone-950 text-white overflow-hidden relative">
        <div class="absolute -left-20 -bottom-20 w-80 h-80 rounded-full bg-rose-600/10 blur-3xl pointer-events-none"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-8 items-center">
                <div class="md:col-span-8 space-y-3">
                    <span class="text-amber-400 text-xs font-extrabold uppercase tracking-widest">Master Craftsmanship</span>
                    <h2 class="text-2xl sm:text-4xl font-extrabold font-serif leading-tight">
                        Engineered for High-Mile Days & 6-Hour Weddings
                    </h2>
                    <p class="text-stone-300 text-sm sm:text-base leading-relaxed max-w-2xl">
                        Every single pair is hand-lasted by experienced artisans using orthopedic arch contours and multi-layer memory foam. Look royal without painful blisters.
                    </p>
                    <div class="flex items-center gap-3 pt-2">
                        <span class="text-xs bg-white/10 px-3 py-1.5 rounded-full border border-white/20 font-mono">Use Code: <strong>FESTIVE26</strong></span>
                        <span class="text-xs text-amber-300 font-semibold">for 10% off on your first order!</span>
                    </div>
                </div>
                <div class="md:col-span-4 flex justify-start md:justify-end">
                    <a
                        href="#categories"
                        class="px-8 py-4 rounded-full bg-white text-rose-950 hover:bg-rose-50 font-extrabold text-sm tracking-wider uppercase shadow-2xl transition-all"
                    >
                        Explore Collection
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- 6. BEST SELLING COLLECTION -->
    <section id="best-selling" class="py-14 sm:py-20 bg-stone-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-10 gap-4">
                <div>
                    <span class="text-xs font-bold text-amber-700 uppercase tracking-widest">Customer Favorites</span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight font-serif mt-1">
                        Best Selling Footwear in Bangladesh
                    </h2>
                </div>
                <a href="#new-arrivals" class="text-xs sm:text-sm font-bold text-rose-900 hover:underline">
                    View Complete Catalog →
                </a>
            </div>

            <!-- Best Sellers Grid -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                @foreach($bestSellers as $product)
                    <article
                        class="group bg-white rounded-2xl border border-stone-200 hover:border-amber-700/30 overflow-hidden shadow-xs hover:shadow-xl transition-all duration-300 flex flex-col justify-between"
                    >
                        <div class="relative aspect-square overflow-hidden bg-stone-100">
                            <img
                                src="{{ $product->image_url }}"
                                alt="{{ $product->name }}"
                                width="600"
                                height="600"
                                loading="lazy"
                                class="w-full h-full object-cover group-hover:scale-106 transition-transform duration-500"
                            >
                            <div class="absolute top-3 left-3">
                                <span class="bg-amber-600 text-white text-[10px] font-extrabold px-2 py-0.5 rounded-full uppercase tracking-wider shadow-xs">
                                    ★ TOP RATED
                                </span>
                            </div>
                            <div class="absolute bottom-3 left-3 bg-white/90 backdrop-blur-xs text-slate-800 text-[10px] font-bold px-2 py-0.5 rounded-full shadow-xs flex items-center gap-1">
                                <span class="text-amber-500">★</span>
                                <span>{{ $product->average_rating }}</span>
                                <span class="text-stone-400 font-normal">({{ $product->reviews_count }})</span>
                            </div>
                        </div>

                        <div class="p-4 flex-1 flex flex-col justify-between">
                            <div>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-stone-500">
                                    {{ $product->category?->name ?? 'Footwear' }}
                                </span>
                                <h3 class="text-sm font-bold text-slate-900 group-hover:text-rose-900 transition-colors line-clamp-1 mt-0.5">
                                    {{ $product->name }}
                                </h3>

                                @if(!empty($product->available_sizes))
                                    <div class="mt-2 flex items-center gap-1 overflow-x-auto no-scrollbar py-0.5">
                                        <span class="text-[10px] text-stone-400 font-medium mr-1">EU:</span>
                                        @foreach(array_slice($product->available_sizes, 0, 5) as $size)
                                            <span class="text-[10px] bg-stone-100 text-stone-700 px-1.5 py-0.5 rounded font-mono font-medium">
                                                {{ $size }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <div class="mt-4 pt-3 border-t border-stone-100 flex items-center justify-between">
                                <div>
                                    <div class="text-sm sm:text-base font-extrabold text-slate-900">
                                        ৳ {{ number_format($product->effective_price) }}
                                    </div>
                                    @if($product->has_discount)
                                        <div class="text-xs text-stone-400 line-through">
                                            ৳ {{ number_format($product->original_price) }}
                                        </div>
                                    @endif
                                </div>

                                @php
                                    $firstVariant = $product->variants->where('stock', '>', 0)->first() ?? $product->variants->first();
                                @endphp
                                <button
                                    type="button"
                                    class="p-2 sm:px-3 sm:py-2 rounded-xl bg-slate-900 hover:bg-rose-900 text-white text-xs font-semibold flex items-center gap-1.5 transition-colors shadow-xs active:scale-95"
                                    @click="quickAdd({{ $firstVariant?->id ?? 0 }})"
                                    title="Add to Shopping Bag"
                                    aria-label="Add {{ $product->name }} to shopping bag"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    <span class="hidden sm:inline">Add</span>
                                </button>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <!-- 7. CUSTOMER REVIEWS (Moderated Social Proof) -->
    <section id="reviews" class="py-14 sm:py-20 bg-white border-t border-stone-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-12">
                <span class="text-xs font-bold text-rose-900 uppercase tracking-widest">Real Customer Love</span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight font-serif mt-1">
                    What Bangladeshi Women Say
                </h2>
                <p class="text-sm text-stone-500 mt-2">
                    Over 15,000+ satisfied shoppers across Dhaka, Chattogram, Sylhet, and all 64 districts.
                </p>
            </div>

            <!-- Reviews Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($reviews as $rev)
                    <div class="bg-stone-50 rounded-2xl p-6 border border-stone-200/80 shadow-2xs hover:shadow-md transition flex flex-col justify-between">
                        <div>
                            <!-- Star Rating -->
                            <div class="flex items-center gap-1 text-amber-500 text-sm">
                                @for($i = 1; $i <= 5; $i++)
                                    <span>{{ $i <= $rev->rating ? '★' : '☆' }}</span>
                                @endfor
                                <span class="ml-2 text-xs font-bold text-slate-700">{{ $rev->rating }}.0</span>
                            </div>

                            <!-- Comment -->
                            <p class="text-sm text-slate-700 mt-3 leading-relaxed italic">
                                "{{ $rev->comment }}"
                            </p>
                        </div>

                        <!-- Customer & Purchased Item Line -->
                        <div class="mt-6 pt-4 border-t border-stone-200/60 flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <div class="w-9 h-9 rounded-full bg-rose-100 text-rose-800 font-bold text-xs flex items-center justify-center">
                                    {{ substr($rev->user?->name ?? 'Verified Customer', 0, 1) }}
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold text-slate-900">{{ $rev->user?->name ?? 'Verified Shopper' }}</h4>
                                    <p class="text-[10px] text-emerald-700 font-medium flex items-center gap-1">
                                        <span>✓</span> Verified Buyer
                                    </p>
                                </div>
                            </div>

                            @if($rev->product)
                                <div class="text-right">
                                    <span class="text-[10px] text-stone-500 line-clamp-1 max-w-[120px]" title="{{ $rev->product->name }}">
                                        {{ $rev->product->name }}
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- 8. RETAIL OUTLETS (Boutiques in Dhaka & Chattogram) -->
    <section id="outlets" class="py-14 sm:py-20 bg-stone-100/70 border-t border-stone-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-10 gap-4">
                <div>
                    <span class="text-xs font-bold text-rose-900 uppercase tracking-widest">Experience In Store</span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight font-serif mt-1">
                        Visit Our Retail Boutiques
                    </h2>
                </div>
                <p class="text-xs sm:text-sm text-stone-500 max-w-sm">
                    Try on our full size range (EU 35–42) in person at our prime locations in Dhaka and Chattogram.
                </p>
            </div>

            <!-- Outlets Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($outlets as $outlet)
                    <div class="bg-white rounded-2xl p-6 border border-stone-200 shadow-xs hover:shadow-md transition flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between pb-3 border-b border-stone-100">
                                <h3 class="text-base font-bold text-slate-900 font-serif">{{ $outlet->name }}</h3>
                                <span class="text-[10px] font-bold text-rose-800 bg-rose-50 px-2.5 py-1 rounded-full uppercase">
                                    {{ $outlet->district ?? $outlet->city ?? 'Dhaka' }}
                                </span>
                            </div>

                            <div class="mt-4 space-y-2.5 text-xs text-stone-600">
                                <p class="flex items-start gap-2">
                                    <span class="text-rose-700 shrink-0 mt-0.5">📍</span>
                                    <span>{{ $outlet->address }}</span>
                                </p>
                                <p class="flex items-center gap-2">
                                    <span class="text-rose-700 shrink-0">🕒</span>
                                    <span>{{ $outlet->opening_hours }}</span>
                                </p>
                                <p class="flex items-center gap-2">
                                    <span class="text-rose-700 shrink-0">📞</span>
                                    <a href="tel:{{ $outlet->phone }}" class="hover:text-rose-900 font-medium">{{ $outlet->phone }}</a>
                                </p>
                            </div>
                        </div>

                        <div class="mt-6 pt-4 border-t border-stone-100 flex items-center justify-between">
                            <span class="text-[11px] text-emerald-700 font-semibold flex items-center gap-1">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                Store Open
                            </span>

                            @if($outlet->map_link)
                                <a
                                    href="{{ $outlet->map_link }}"
                                    target="_blank"
                                    rel="noopener"
                                    class="inline-flex items-center gap-1 text-xs font-bold text-rose-900 hover:text-rose-700 bg-rose-50 hover:bg-rose-100 px-3 py-1.5 rounded-lg transition"
                                >
                                    <span>Get Directions</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- 9. VIP CLUB & NEWSLETTER SUBSCRIPTION -->
    <section class="py-14 sm:py-16 bg-white border-t border-stone-200">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-4">
            <span class="text-xs font-bold text-rose-900 uppercase tracking-widest">VIP Footwear Club</span>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 font-serif">
                Be First in Line for Festive Drops & Private Sales
            </h2>
            <p class="text-xs sm:text-sm text-stone-500 max-w-lg mx-auto">
                Subscribe for early access to our seasonal Eid drops, exclusive restock notices for viral Boston clogs, and member-only secret discount codes.
            </p>

            <form
                class="pt-3 max-w-md mx-auto flex flex-col sm:flex-row gap-2"
                @submit.prevent="notify('✓ Thank you for subscribing to the VIP Club!')"
            >
                <input
                    type="email"
                    required
                    placeholder="Enter your email or phone number..."
                    class="flex-1 px-4 py-3 rounded-full bg-stone-50 border border-stone-300 text-sm focus:outline-hidden focus:ring-2 focus:ring-rose-800/40 text-slate-800 placeholder:text-stone-400"
                >
                <button
                    type="submit"
                    class="px-7 py-3 rounded-full bg-rose-900 hover:bg-rose-800 text-white font-bold text-xs uppercase tracking-wider shadow-sm transition"
                >
                    Subscribe
                </button>
            </form>
            <p class="text-[11px] text-stone-400">Zero spam. You can unsubscribe anytime.</p>
        </div>
    </section>

@endsection
