@extends('layouts.storefront')

@section('title', ($storeSettings['shop_name'] ?? 'Heel Bari') . ' | Handcrafted Women\'s Footwear Bangladesh')
@section('canonical_url', route('home'))

@section('content')

    <!-- 1. HERO BANNER SLIDER (Full-Bleed Widescreen Footwear Showcase Inspired by Myro BD) -->
    <section
        id="hero-slider"
        class="relative overflow-hidden bg-stone-950 text-white select-none"
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
                }, 6000);
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
        <!-- Slides Container (Widescreen 16:9 / Full Viewport Fit) -->
        <div class="relative h-[480px] sm:h-[540px] md:h-[580px] lg:h-[620px] xl:h-[660px] max-h-[85vh] w-full overflow-hidden">
            @foreach($banners as $index => $banner)
                @php
                    $editorialDetails = [
                        'Step Into Festive Elegance' => [
                            'tagline_quote' => '“FESTIVE ELEGANCE.”',
                            'brand_accent' => 'Elevated Style with Heel Bari',
                            'pill_badge' => 'FESTIVE COLLECTION 2026',
                            'subtitle' => 'Handcrafted velvet and metallic block heels engineered with cloud foam footbeds for celebration nights in Bangladesh.',
                            'highlight_1' => '☁ 3-Layer Cloud Foam',
                            'highlight_2' => '✨ Handcrafted in BD',
                            'highlight_3' => '✓ 6-Hr Pain-Free Wear',
                            'floating_shoe_tag' => 'Rose-Gold Festive Block Heels',
                            'price_tag' => '৳ 2,490',
                        ],
                        'Cloud-Soft Walking Comfort' => [
                            'tagline_quote' => '“EVERYDAY COMFORT.”',
                            'brand_accent' => 'Elevated Style with Heel Bari',
                            'pill_badge' => 'DAILY STAPLES & COMMUTE',
                            'subtitle' => 'Breathable suede Boston clogs and lightweight slides designed to handle your busiest Dhaka commute with zero foot fatigue.',
                            'highlight_1' => '🌿 Ergonomic Cork Insole',
                            'highlight_2' => '👞 Genuine Suede Leather',
                            'highlight_3' => '✓ Zero Foot Fatigue',
                            'floating_shoe_tag' => 'Caramel Suede Boston Clogs',
                            'price_tag' => '৳ 1,950',
                        ],
                        'Heritage Artisanal Kolhapuri' => [
                            'tagline_quote' => '“HERITAGE ARTISANAL.”',
                            'brand_accent' => 'Crafted Tradition, Modern Grace',
                            'pill_badge' => 'ROYAL ARTISANAL CRAFT',
                            'subtitle' => 'Intricate metallic zari resham threading and mirror accents crafted by master shoemakers in old Dhaka.',
                            'highlight_1' => '👑 Hand-Embroidered Zari',
                            'highlight_2' => '⚜ Pure Genuine Leather',
                            'highlight_3' => '✓ Old Dhaka Masterwork',
                            'floating_shoe_tag' => 'Artisanal Mirror Kolhapuri',
                            'price_tag' => '৳ 1,850',
                        ],
                    ];
                    $editorial = $editorialDetails[$banner->title] ?? [
                        'tagline_quote' => '“ELEVATED COMFORT.”',
                        'brand_accent' => 'Elevated Style with Heel Bari',
                        'pill_badge' => $banner->badge ?? 'NEW ARRIVALS 2026',
                        'subtitle' => $banner->subtitle ?? 'Handcrafted women\'s footwear in Bangladesh.',
                        'highlight_1' => '☁ Cloud Foam Insole',
                        'highlight_2' => '✨ Handcrafted BD',
                        'highlight_3' => '✓ Cash on Delivery',
                        'floating_shoe_tag' => 'Handcrafted Signature Footwear',
                        'price_tag' => '৳ 1,990',
                    ];
                @endphp

                <div
                    class="absolute inset-0"
                    x-show="activeSlide === {{ $index }}"
                    x-transition:enter="transition-opacity ease-out duration-400"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition-opacity ease-in duration-150"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    x-cloak
                >
                    <!-- 1. Full-Bleed Shoes & Model Photography Background (Anchored to Bottom to Keep Shoes Fully Visible) -->
                    <picture class="absolute inset-0 w-full h-full">
                        <source media="(max-width: 640px)" srcset="{{ $banner->mobile_image_url }}">
                        <img
                            src="{{ $banner->image_url }}"
                            alt="{{ $banner->title }} - Heel Bari Footwear"
                            width="1600"
                            height="900"
                            {{ $index === 0 ? 'fetchpriority=high' : 'loading=lazy' }}
                            class="w-full h-full object-cover object-bottom sm:object-[center_bottom] lg:object-[80%_bottom]"
                        >
                    </picture>

                    <!-- 2. Studio Dark Gradients for Text Legibility (Upper Left Gradient to Protect Shoes Below) -->
                    <div class="absolute inset-0 bg-gradient-to-r from-stone-950/90 via-stone-950/60 via-45% to-transparent hidden sm:block pointer-events-none"></div>
                    <div class="absolute inset-0 bg-gradient-to-b from-stone-950/90 via-stone-950/60 via-45% to-transparent sm:hidden pointer-events-none"></div>

                    <!-- 3. Ambient Lighting Accents -->
                    <div class="absolute -left-20 top-1/4 w-96 h-96 rounded-full bg-rose-600/15 blur-3xl pointer-events-none"></div>

                    <!-- 4. Editorial Foreground Content (Text in Upper-Left, Footwear on Floor Remains Clean & Clear) -->
                    <div class="relative z-10 h-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-start pt-8 sm:pt-12 lg:pt-16 pb-4">
                        <div class="max-w-xl sm:max-w-xl lg:max-w-2xl space-y-3 sm:space-y-4 text-left">

                            <!-- Top Floating Badge & Collection Title -->
                            <div class="flex items-center gap-3 flex-wrap">
                                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-rose-500/25 text-rose-300 border border-rose-400/40 text-[11px] font-bold tracking-widest uppercase shadow-lg backdrop-blur-md">
                                    <span class="w-2 h-2 rounded-full bg-rose-400 animate-ping"></span>
                                    <span>{{ $editorial['pill_badge'] }}</span>
                                </div>
                                <span class="text-xs font-bold tracking-wider text-rose-200/90 uppercase drop-shadow-sm">
                                    {{ $banner->title }}
                                </span>
                            </div>

                            <!-- Big Bold Editorial Footwear Headline (Myro Style) -->
                            <h1 class="text-3xl sm:text-4xl lg:text-5xl xl:text-6xl font-black tracking-tight text-white leading-[1.08] font-serif uppercase">
                                <span class="text-rose-400">{{ $editorial['tagline_quote'] }}</span>
                                <span class="block text-lg sm:text-2xl lg:text-3xl font-light italic text-stone-200 tracking-normal mt-1 normal-case font-serif">
                                    {{ $editorial['brand_accent'] }}.
                                </span>
                            </h1>

                            <!-- Footwear Story Subtitle -->
                            <p class="text-xs sm:text-sm lg:text-base text-stone-300 max-w-lg font-normal leading-relaxed line-clamp-2 sm:line-clamp-none">
                                {{ $editorial['subtitle'] }}
                            </p>

                            <!-- Micro Feature Highlights -->
                            <div class="flex flex-wrap items-center gap-2 pt-0.5">
                                <span class="px-3 py-1 rounded-lg bg-black/60 backdrop-blur-md text-[11px] font-semibold text-stone-200 border border-white/15 shadow-sm">
                                    {{ $editorial['highlight_1'] }}
                                </span>
                                <span class="px-3 py-1 rounded-lg bg-black/60 backdrop-blur-md text-[11px] font-semibold text-stone-200 border border-white/15 shadow-sm">
                                    {{ $editorial['highlight_2'] }}
                                </span>
                                <span class="px-3 py-1 rounded-lg bg-black/60 backdrop-blur-md text-[11px] font-semibold text-stone-200 border border-white/15 shadow-sm">
                                    {{ $editorial['highlight_3'] }}
                                </span>
                            </div>

                            <!-- CTA Action Buttons -->
                            <div class="pt-1.5 flex flex-row items-center gap-3">
                                <a
                                    href="{{ $banner->button_link ?? '#new-arrivals' }}"
                                    class="px-7 sm:px-8 py-3 sm:py-3.5 rounded-full bg-rose-800 hover:bg-rose-700 text-white font-extrabold text-xs sm:text-sm tracking-wider uppercase shadow-2xl hover:shadow-rose-900/50 transition-all transform hover:-translate-y-0.5 text-center flex items-center justify-center gap-2"
                                >
                                    <span>{{ $banner->button_text ?? 'Shop Collection' }}</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                </a>

                                <a
                                    href="#categories"
                                    class="px-5 sm:px-7 py-3 sm:py-3.5 rounded-full bg-white/10 hover:bg-white/20 text-stone-200 font-semibold text-xs sm:text-sm border border-white/25 backdrop-blur-md transition text-center"
                                >
                                    Browse Categories
                                </a>
                            </div>

                            <!-- Trust Micro Line -->
                            <div class="pt-1 flex items-center gap-4 text-xs text-stone-300">
                                <span class="flex items-center gap-1.5 font-medium">
                                    <span class="text-amber-400">★</span> 4.9/5 Rating
                                </span>
                                <span class="flex items-center gap-1.5 font-medium">
                                    <span class="text-emerald-400">✓</span> Cash on Delivery
                                </span>
                                <span class="flex items-center gap-1.5 font-medium">
                                    <span class="text-rose-400">🔄</span> 7-Day Exchange
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Slider Controls (Myro-style circular navigation arrows) -->
        @if($banners->count() > 1)
            <!-- Prev Arrow -->
            <button
                type="button"
                class="absolute left-4 sm:left-6 top-1/2 -translate-y-1/2 z-30 w-11 h-11 sm:w-12 sm:h-12 rounded-full border border-white/30 bg-black/40 hover:bg-black/80 backdrop-blur-md text-white flex items-center justify-center transition-all duration-300 shadow-xl cursor-pointer hover:scale-105 active:scale-95"
                @click="prevSlide()"
                aria-label="Previous Slide"
            >
                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </button>

            <!-- Next Arrow -->
            <button
                type="button"
                class="absolute right-4 sm:right-6 top-1/2 -translate-y-1/2 z-30 w-11 h-11 sm:w-12 sm:h-12 rounded-full border border-white/30 bg-black/40 hover:bg-black/80 backdrop-blur-md text-white flex items-center justify-center transition-all duration-300 shadow-xl cursor-pointer hover:scale-105 active:scale-95"
                @click="nextSlide()"
                aria-label="Next Slide"
            >
                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>

            <!-- Bottom Progress / Indicator Bar -->
            <div class="absolute bottom-4 sm:bottom-5 inset-x-0 z-30 flex justify-center items-center gap-2">
                @foreach($banners as $index => $banner)
                    <button
                        type="button"
                        class="h-1.5 sm:h-2 rounded-full transition-all duration-500 cursor-pointer"
                        :class="activeSlide === {{ $index }} ? 'w-8 sm:w-10 bg-rose-500 shadow-lg shadow-rose-500/50' : 'w-2 sm:w-2.5 bg-white/35 hover:bg-white/70'"
                        @click="goToSlide({{ $index }})"
                        aria-label="Go to slide {{ $index + 1 }}"
                    ></button>
                @endforeach
            </div>
        @endif
    </section>

    <!-- 2. TRUST STRIP (Easy Exchange / Cash on Delivery / Fast Delivery / Cloud Cushion) -->
    <section id="trust-strip" class="bg-white border-b border-stone-200 py-6 sm:py-8 shadow-2xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-8">
                <!-- Trust Item 1: Easy Exchange -->
                <div class="flex items-center gap-3.5 p-2 rounded-2xl">
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-800 flex items-center justify-center text-2xl shrink-0 border border-amber-200/60 shadow-xs">
                        🔄
                    </div>
                    <div>
                        <h4 class="text-xs sm:text-sm font-bold text-slate-900 font-serif">Easy 7-Day Exchange</h4>
                        <p class="text-[11px] text-stone-500">Doorstep size swap across all 64 districts</p>
                    </div>
                </div>

                <!-- Trust Item 2: Cash on Delivery -->
                <div class="flex items-center gap-3.5 p-2 rounded-2xl">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-800 flex items-center justify-center text-2xl shrink-0 border border-emerald-200/60 shadow-xs">
                        💵
                    </div>
                    <div>
                        <h4 class="text-xs sm:text-sm font-bold text-slate-900 font-serif">Cash on Delivery</h4>
                        <p class="text-[11px] text-stone-500">Inspect shoes at home before making payment</p>
                    </div>
                </div>

                <!-- Trust Item 3: Fast Delivery -->
                <div class="flex items-center gap-3.5 p-2 rounded-2xl">
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-800 flex items-center justify-center text-2xl shrink-0 border border-blue-200/60 shadow-xs">
                        🚚
                    </div>
                    <div>
                        <h4 class="text-xs sm:text-sm font-bold text-slate-900 font-serif">Fast Local Delivery</h4>
                        <p class="text-[11px] text-stone-500">24–48h Dhaka dispatch • 3–5 days nationwide</p>
                    </div>
                </div>

                <!-- Trust Item 4: Cloud Cushion -->
                <div class="flex items-center gap-3.5 p-2 rounded-2xl">
                    <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-800 flex items-center justify-center text-2xl shrink-0 border border-rose-200/60 shadow-xs">
                        ☁️
                    </div>
                    <div>
                        <h4 class="text-xs sm:text-sm font-bold text-slate-900 font-serif">Cloud Foam Insole</h4>
                        <p class="text-[11px] text-stone-500">High-rebound memory foam for all-day poise</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 3. LARGE IMAGE CATEGORY TILES (Editorial 4:5 Cards with Counts & Descriptions) -->
    <section id="categories" class="py-14 sm:py-20 bg-stone-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-10 gap-4">
                <div>
                    <span class="text-xs font-bold text-rose-900 uppercase tracking-widest">Handcrafted Collections</span>
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-slate-900 tracking-tight font-serif mt-1">
                        Explore Footwear by Category
                    </h2>
                </div>
                <p class="text-xs sm:text-sm text-stone-500 max-w-sm">
                    From supportive 6-hour festive block heels to everyday cloud-soft clogs, find your ideal silhouette.
                </p>
            </div>

            <!-- Categories Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
                @foreach($categories as $category)
                    <div
                        id="category-{{ $category->slug }}"
                        class="group relative bg-white rounded-3xl overflow-hidden shadow-xs hover:shadow-xl border border-stone-200/80 transition-all duration-300 flex flex-col"
                    >
                        <!-- Large Image Container (4:5 Aspect Ratio) -->
                        <div class="relative aspect-[4/5] overflow-hidden bg-stone-100">
                            <a href="{{ route('category.show', $category->slug) }}" class="block w-full h-full">
                                <img
                                    src="{{ $category->image_url }}"
                                    alt="{{ $category->name }} - Heel Bari Footwear"
                                    width="800"
                                    height="1000"
                                    loading="lazy"
                                    class="w-full h-full object-cover group-hover:scale-106 transition-transform duration-500"
                                >
                            </a>

                            <!-- Product count badge -->
                            <div class="absolute top-3 right-3 bg-white/95 backdrop-blur-xs text-slate-900 text-[10px] font-bold px-2.5 py-1 rounded-full shadow-xs">
                                {{ $category->products_count }} pairs
                            </div>
                        </div>

                        <!-- Card Content -->
                        <div class="p-4 sm:p-5 flex-1 flex flex-col justify-between">
                            <div>
                                <h3 class="text-base sm:text-lg font-bold text-slate-900 group-hover:text-rose-950 transition-colors font-serif">
                                    <a href="{{ route('category.show', $category->slug) }}">
                                        {{ $category->name }}
                                    </a>
                                </h3>
                                <p class="text-xs text-stone-500 mt-1 line-clamp-2 leading-relaxed">
                                    {{ $category->description ?? 'Handcrafted women footwear with cushioned memory foam footbeds.' }}
                                </p>
                            </div>

                            <!-- Subcategories Pills & CTA -->
                            <div class="mt-4 pt-3 border-t border-stone-100 flex items-center justify-between">
                                <a
                                    href="{{ route('category.show', $category->slug) }}"
                                    class="text-xs font-bold text-rose-950 group-hover:text-rose-700 flex items-center gap-1 transition"
                                >
                                    <span>Shop Collection</span>
                                    <span>→</span>
                                </a>

                                @if($category->children->isNotEmpty())
                                    <span class="text-[10px] text-stone-400 font-medium">
                                        {{ $category->children->count() }} styles
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- 4. NEW ARRIVALS (Product Cards with Dual-Image Hover, % Off, Badges, Size Chips) -->
    <section id="new-arrivals" class="py-14 sm:py-20 bg-white border-t border-stone-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-10 gap-4">
                <div>
                    <div class="inline-flex items-center gap-2 text-rose-800 text-xs font-bold uppercase tracking-widest mb-1">
                        <span class="w-2 h-2 rounded-full bg-rose-600 animate-pulse"></span>
                        <span>Just Dropped</span>
                    </div>
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-slate-900 tracking-tight font-serif">
                        New Festive and Everyday Arrivals
                    </h2>
                </div>
                <a href="{{ route('products.index') }}" class="text-xs sm:text-sm font-bold text-rose-900 hover:underline">
                    View Complete Catalog ({{ \App\Models\Product::active()->count() }} pairs) →
                </a>
            </div>

            <!-- Products Grid with New Reusable Component -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                @foreach($newArrivals as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
        </div>
    </section>

    <!-- 5. SHOP BY OCCASION (Wedding, Party, Office, Casual) -->
    <section id="occasions" class="py-14 sm:py-20 bg-[#FAF7F2] border-t border-stone-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-12">
                <span class="text-xs font-bold text-rose-900 uppercase tracking-widest">Tailored For Every Moment</span>
                <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-slate-900 tracking-tight font-serif mt-1">
                    Shop Footwear by Occasion
                </h2>
                <p class="text-xs sm:text-sm text-stone-500 mt-2">
                    Whether you are preparing for a gala wedding, a full workday in Dhaka, or a relaxed weekend coffee date.
                </p>
            </div>

            <!-- 4 Occasion Tiles Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($occasions as $occ)
                    <div class="group relative bg-white rounded-3xl overflow-hidden shadow-xs hover:shadow-xl border border-stone-200/90 transition-all duration-300 flex flex-col justify-between">
                        <!-- Image Container -->
                        <div class="relative aspect-[4/3] overflow-hidden bg-stone-100">
                            <img
                                src="{{ $occ['image'] }}"
                                alt="{{ $occ['title'] }} - Heel Bari Footwear"
                                width="600"
                                height="450"
                                loading="lazy"
                                class="w-full h-full object-cover group-hover:scale-108 transition-transform duration-500"
                            >
                            <div class="absolute top-3 left-3">
                                <span class="bg-rose-950/85 backdrop-blur-xs text-white text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider">
                                    {{ $occ['badge'] }}
                                </span>
                            </div>
                        </div>

                        <!-- Card Details -->
                        <div class="p-5 flex-1 flex flex-col justify-between">
                            <div>
                                <h3 class="text-lg font-bold text-slate-900 group-hover:text-rose-950 transition-colors font-serif">
                                    {{ $occ['title'] }}
                                </h3>
                                <p class="text-xs text-stone-500 mt-1.5 leading-relaxed">
                                    {{ $occ['subtitle'] }}
                                </p>
                            </div>

                            <div class="mt-5 pt-3 border-t border-stone-100">
                                <a
                                    href="{{ $occ['link'] }}"
                                    class="w-full py-2.5 rounded-xl bg-stone-100 hover:bg-rose-950 hover:text-white text-slate-800 text-xs font-bold transition flex items-center justify-center gap-1.5"
                                >
                                    <span>Explore Collection</span>
                                    <span>→</span>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- 6. BEST SELLERS SECTION -->
    <section id="best-selling" class="py-14 sm:py-20 bg-white border-t border-stone-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-10 gap-4">
                <div>
                    <span class="text-xs font-bold text-[#C5A059] uppercase tracking-widest">Customer Favorites</span>
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-slate-900 tracking-tight font-serif mt-1">
                        Best Selling Footwear in Bangladesh
                    </h2>
                </div>
                <div class="text-xs sm:text-sm text-stone-500">
                    Trusted by 15,000+ satisfied Bangladeshi women
                </div>
            </div>

            <!-- Best Sellers Grid -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                @foreach($bestSellers as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
        </div>
    </section>

    <!-- 7. OUTLET & CLEARANCE SECTION (Discounted & End-of-Season Pairs) -->
    <section id="outlet-section" class="py-14 sm:py-20 bg-gradient-to-br from-stone-900 via-rose-950 to-stone-950 text-white relative overflow-hidden">
        <div class="absolute -right-20 -bottom-20 w-96 h-96 rounded-full bg-rose-500/10 blur-3xl pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-10 gap-6">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-rose-500/20 text-rose-300 border border-rose-400/30 text-[10px] font-bold tracking-widest uppercase mb-2">
                        <span class="w-2 h-2 rounded-full bg-rose-400 animate-pulse"></span>
                        <span>Outlet Studio & Archive</span>
                    </div>
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold font-serif">
                        Limited Quantities • Up to 25% OFF
                    </h2>
                    <p class="text-xs sm:text-sm text-stone-300 mt-2 max-w-xl">
                        Handcrafted archive pairs and seasonal restocks with full 7-day size exchange guarantee. Grab your size before it sells out.
                    </p>
                </div>

                <a
                    href="{{ route('products.index') }}"
                    class="px-6 py-3 rounded-full bg-white text-rose-950 hover:bg-rose-50 font-extrabold text-xs uppercase tracking-wider transition shrink-0 text-center"
                >
                    View All Outlet Deals →
                </a>
            </div>

            <!-- Outlet Products Grid -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                @foreach($outletProducts as $product)
                    <div class="bg-white rounded-3xl p-1 text-slate-800">
                        <x-product-card :product="$product" />
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- 8. CUSTOMER REVIEWS WITH PHOTOS (Social Proof & Atelier Stories) -->
    <section id="reviews" class="py-16 sm:py-24 bg-gradient-to-b from-stone-50 via-rose-50/30 to-white relative overflow-hidden border-t border-stone-200">
        <!-- Ambient decorative blurs -->
        <div class="pointer-events-none absolute -top-24 right-0 w-96 h-96 bg-rose-300/15 rounded-full blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-24 -left-20 w-80 h-80 bg-amber-300/15 rounded-full blur-3xl"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <!-- Header with Rating Pill -->
            <div class="text-center max-w-3xl mx-auto mb-12 sm:mb-16">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-rose-900/10 border border-rose-900/20 text-rose-950 text-xs font-extrabold tracking-widest uppercase mb-3 shadow-2xs">
                    <div class="flex text-amber-500 gap-0.5">
                        @for($s = 0; $s < 5; $s++)
                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        @endfor
                    </div>
                    <span>4.9 / 5.0 Rated by 15,000+ Shoppers</span>
                </div>

                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-slate-900 tracking-tight font-serif">
                    What Bangladeshi Women Say
                </h2>
                <p class="text-xs sm:text-base text-stone-600 mt-3 max-w-xl mx-auto leading-relaxed">
                    Real, unedited feedback from brides, corporate power-dressers, and everyday walkers across all 64 districts.
                </p>

                <!-- Trust Metrics Highlight Strip -->
                <div class="mt-8 grid grid-cols-2 sm:grid-cols-4 gap-3 max-w-4xl mx-auto">
                    <div class="bg-white/80 backdrop-blur-xs rounded-2xl p-3 sm:p-3.5 border border-stone-200/80 shadow-2xs text-center">
                        <span class="block text-lg sm:text-xl font-black text-rose-950 font-serif">98.6%</span>
                        <span class="text-[11px] font-semibold text-stone-600">Fit & Comfort Score</span>
                    </div>
                    <div class="bg-white/80 backdrop-blur-xs rounded-2xl p-3 sm:p-3.5 border border-stone-200/80 shadow-2xs text-center">
                        <span class="block text-lg sm:text-xl font-black text-rose-950 font-serif">6-Hour+</span>
                        <span class="text-[11px] font-semibold text-stone-600">Pain-Free Event Wear</span>
                    </div>
                    <div class="bg-white/80 backdrop-blur-xs rounded-2xl p-3 sm:p-3.5 border border-stone-200/80 shadow-2xs text-center">
                        <span class="block text-lg sm:text-xl font-black text-rose-950 font-serif">24–48h</span>
                        <span class="text-[11px] font-semibold text-stone-600">Dhaka & Nationwide</span>
                    </div>
                    <div class="bg-white/80 backdrop-blur-xs rounded-2xl p-3 sm:p-3.5 border border-stone-200/80 shadow-2xs text-center">
                        <span class="block text-lg sm:text-xl font-black text-rose-950 font-serif">7-Day</span>
                        <span class="text-[11px] font-semibold text-stone-600">Doorstep Size Exchange</span>
                    </div>
                </div>
            </div>

            <!-- Reviews Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-7">
                @foreach($reviews as $rev)
                    <div class="relative bg-white rounded-3xl p-6 sm:p-7 border border-stone-200/90 shadow-sm hover:shadow-xl hover:-translate-y-1.5 transition-all duration-300 flex flex-col justify-between group overflow-hidden">
                        <!-- Top Accent Subtle Bar on Hover -->
                        <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-rose-700 via-amber-500 to-rose-700 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

                        <!-- Decorative Large Watermark Quote -->
                        <div class="absolute top-4 right-6 text-stone-100 group-hover:text-rose-100/60 transition-colors pointer-events-none select-none">
                            <svg class="w-14 h-14" fill="currentColor" viewBox="0 0 24 24"><path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/></svg>
                        </div>

                        <div class="relative z-10">
                            <!-- Star Rating & Badge -->
                            <div class="flex items-center justify-between gap-2 mb-3">
                                <div class="flex items-center gap-1">
                                    <div class="flex text-amber-400 gap-0.5">
                                        @for($i = 1; $i <= 5; $i++)
                                            <svg class="w-4 h-4 {{ $i <= $rev->rating ? 'fill-current text-amber-400' : 'text-stone-300 fill-stone-300' }}" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                        @endfor
                                    </div>
                                    <span class="text-xs font-bold text-slate-800 ml-1.5">{{ $rev->rating }}.0</span>
                                </div>
                                <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-800 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200/60">
                                    <svg class="w-3 h-3 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                    Verified Order
                                </span>
                            </div>

                            <!-- Customer Comment -->
                            <blockquote class="text-slate-800 text-sm sm:text-base leading-relaxed font-serif italic mt-3">
                                "{{ $rev->comment }}"
                            </blockquote>

                            <!-- Reviewed Product Snapshot Chip -->
                            @if($rev->product)
                                <a
                                    href="{{ route('product.show', $rev->product->slug) }}"
                                    class="mt-5 flex items-center gap-3 p-2.5 rounded-2xl bg-stone-50 hover:bg-rose-50/70 border border-stone-200/70 transition group/prod"
                                    title="View {{ $rev->product->name }}"
                                >
                                    <img
                                        src="{{ $rev->product->image_url }}"
                                        alt="{{ $rev->product->name }}"
                                        class="w-12 h-12 rounded-xl object-cover border border-stone-200/80 shadow-2xs group-hover/prod:scale-105 transition-transform"
                                    >
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center justify-between">
                                            <span class="text-[10px] font-bold text-rose-900 uppercase tracking-wider">Reviewed Item</span>
                                            <span class="text-[11px] font-extrabold text-slate-900">৳{{ number_format($rev->product->effective_price, 0) }}</span>
                                        </div>
                                        <p class="text-xs font-bold text-slate-800 line-clamp-1 group-hover/prod:text-rose-900 transition">
                                            {{ $rev->product->name }}
                                        </p>
                                    </div>
                                </a>
                            @endif
                        </div>

                        <!-- Customer Profile Footer -->
                        <div class="mt-6 pt-4 border-t border-stone-100 flex items-center justify-between relative z-10">
                            <div class="flex items-center gap-3">
                                @if(!empty($rev->user?->avatar_url))
                                    <img
                                        src="{{ $rev->user->avatar_url }}"
                                        alt="{{ $rev->user->name }}"
                                        class="w-11 h-11 rounded-full object-cover ring-2 ring-rose-200 ring-offset-2 shadow-2xs"
                                    >
                                @else
                                    <div class="w-11 h-11 rounded-full bg-gradient-to-tr from-rose-900 to-rose-700 text-white font-bold text-sm flex items-center justify-center ring-2 ring-rose-200 ring-offset-2 shadow-2xs">
                                        {{ substr($rev->user?->name ?? 'V', 0, 1) }}
                                    </div>
                                @endif
                                <div>
                                    <h4 class="text-xs sm:text-sm font-extrabold text-slate-900 leading-tight">
                                        {{ $rev->user?->name ?? 'Verified Shopper' }}
                                    </h4>
                                    <p class="text-[11px] text-stone-500 font-medium flex items-center gap-1 mt-0.5">
                                        <span>📍 {{ $rev->user?->defaultAddress?->district ?? 'Dhaka, BD' }}</span>
                                    </p>
                                </div>
                            </div>

                            <!-- Heart Badge -->
                            <div class="w-8 h-8 rounded-full bg-rose-50 text-rose-700 flex items-center justify-center shadow-2xs">
                                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Guarantee Callout Ribbon -->
            <div class="mt-12 sm:mt-16 bg-gradient-to-r from-rose-950 via-neutral-900 to-rose-950 rounded-3xl p-6 sm:p-8 text-white flex flex-col sm:flex-row items-center justify-between gap-6 shadow-xl border border-rose-900/40">
                <div class="flex items-center gap-4 text-center sm:text-left">
                    <div class="w-14 h-14 rounded-2xl bg-amber-500/20 border border-amber-400/30 text-amber-300 flex items-center justify-center shrink-0">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <div>
                        <h4 class="text-base sm:text-lg font-bold font-serif text-white">Worry-Free Size & Fit Assurance</h4>
                        <p class="text-xs sm:text-sm text-stone-300 mt-1 max-w-xl">
                            If your pair doesn't fit like a glove, our delivery rider will swap it at your doorstep within 7 days in Dhaka, or via hassle-free nationwide courier exchange.
                        </p>
                    </div>
                </div>
                <a
                    href="#categories"
                    class="shrink-0 px-6 py-3 rounded-full bg-rose-800 hover:bg-rose-700 text-white font-extrabold text-xs uppercase tracking-wider transition shadow-md"
                >
                    Find Your Fit
                </a>
            </div>
        </div>
    </section>

    <!-- 9. RETAIL BOUTIQUES / OUTLETS (High-End Atelier Showcase) -->
    <section
        id="outlets"
        class="py-16 sm:py-24 bg-stone-950 text-white relative overflow-hidden border-t border-stone-800"
        x-data="{ filterDistrict: 'all' }"
    >
        <!-- Background Ambient Luxury Lighting -->
        <div class="absolute -top-32 left-1/4 w-[500px] h-[500px] rounded-full bg-rose-900/15 blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 right-10 w-[450px] h-[450px] rounded-full bg-amber-600/10 blur-3xl pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <!-- Header Section -->
            <div class="flex flex-col lg:flex-row lg:items-end justify-between mb-10 sm:mb-12 gap-6">
                <div>
                    <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-rose-500/20 text-rose-300 border border-rose-400/30 text-[11px] font-bold tracking-widest uppercase mb-3">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span>IN-STORE EXPERIENCE & FITTING LOUNGES</span>
                    </div>
                    <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-white tracking-tight font-serif">
                        Visit Our Retail Boutiques
                    </h2>
                    <p class="text-xs sm:text-sm text-stone-400 mt-2.5 max-w-xl leading-relaxed">
                        Step into our curated ateliers. Feel our cloud-cushioned footbeds, try the complete EU 35–42 size run, and experience personalized bridal and festive styling.
                    </p>
                </div>

                <!-- Interactive District Filters -->
                <div class="flex items-center gap-2 flex-wrap">
                    <button
                        type="button"
                        @click="filterDistrict = 'all'"
                        class="px-4 py-2 rounded-full text-xs font-extrabold uppercase tracking-wider transition cursor-pointer"
                        :class="filterDistrict === 'all' ? 'bg-rose-900 text-white shadow-lg shadow-rose-950/60 ring-2 ring-rose-600' : 'bg-stone-900 text-stone-300 hover:bg-stone-800 border border-stone-800'"
                    >
                        All Boutiques ({{ $outlets->count() }})
                    </button>
                    <button
                        type="button"
                        @click="filterDistrict = 'Dhaka'"
                        class="px-4 py-2 rounded-full text-xs font-extrabold uppercase tracking-wider transition cursor-pointer"
                        :class="filterDistrict === 'Dhaka' ? 'bg-rose-900 text-white shadow-lg shadow-rose-950/60 ring-2 ring-rose-600' : 'bg-stone-900 text-stone-300 hover:bg-stone-800 border border-stone-800'"
                    >
                        Dhaka Flagships ({{ $outlets->where('district', 'Dhaka')->count() }})
                    </button>
                    <button
                        type="button"
                        @click="filterDistrict = 'Chittagong'"
                        class="px-4 py-2 rounded-full text-xs font-extrabold uppercase tracking-wider transition cursor-pointer"
                        :class="filterDistrict === 'Chittagong' ? 'bg-rose-900 text-white shadow-lg shadow-rose-950/60 ring-2 ring-rose-600' : 'bg-stone-900 text-stone-300 hover:bg-stone-800 border border-stone-800'"
                    >
                        Chattogram ({{ $outlets->where('district', 'Chittagong')->count() }})
                    </button>
                </div>
            </div>

            <!-- Outlets Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-7">
                @foreach($outlets as $outlet)
                    <div
                        class="bg-stone-900/90 rounded-3xl overflow-hidden border border-stone-800 hover:border-rose-700/60 shadow-xl hover:shadow-2xl hover:-translate-y-1.5 transition-all duration-300 flex flex-col justify-between group"
                        x-show="filterDistrict === 'all' || filterDistrict === '{{ $outlet->district }}'"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                    >
                        <!-- Boutique Storefront Photography Showcase -->
                        <div class="relative h-48 sm:h-52 overflow-hidden bg-stone-950">
                            <img
                                src="{{ $outlet->image_url }}"
                                alt="{{ $outlet->name }}"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700"
                                loading="lazy"
                            >
                            <div class="absolute inset-0 bg-gradient-to-t from-stone-900 via-stone-900/30 to-transparent"></div>

                            <!-- City Badge -->
                            <div class="absolute top-3.5 left-3.5">
                                <span class="text-[10px] font-black uppercase tracking-widest text-rose-200 bg-rose-950/80 backdrop-blur-md px-3 py-1 rounded-full border border-rose-800/60 shadow-xs">
                                    {{ $outlet->district === 'Chittagong' ? 'Chattogram' : 'Dhaka' }}
                                </span>
                            </div>

                            <!-- Live Open Indicator -->
                            <div class="absolute top-3.5 right-3.5">
                                <span class="inline-flex items-center gap-1.5 text-[10px] font-bold text-emerald-300 bg-stone-950/85 backdrop-blur-md px-2.5 py-1 rounded-full border border-emerald-500/30 shadow-xs">
                                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                    <span>Store Open</span>
                                </span>
                            </div>

                            <!-- Bottom Title inside banner overlay -->
                            <div class="absolute bottom-3 left-4 right-4">
                                <h3 class="text-base sm:text-lg font-bold text-white font-serif group-hover:text-rose-300 transition line-clamp-1">
                                    {{ $outlet->name }}
                                </h3>
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="p-6 flex-1 flex flex-col justify-between space-y-4">
                            <div class="space-y-3 text-xs text-stone-300">
                                <!-- Address -->
                                <div class="flex items-start gap-2.5">
                                    <div class="w-6 h-6 rounded-lg bg-rose-950/70 border border-rose-900/40 text-rose-400 flex items-center justify-center shrink-0 mt-0.5">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    </div>
                                    <span class="leading-relaxed text-stone-300">{{ $outlet->address }}</span>
                                </div>

                                <!-- Hours -->
                                <div class="flex items-center gap-2.5">
                                    <div class="w-6 h-6 rounded-lg bg-amber-950/70 border border-amber-900/40 text-amber-400 flex items-center justify-center shrink-0">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </div>
                                    <span class="text-stone-300">{{ $outlet->opening_hours }}</span>
                                </div>

                                <!-- Phone -->
                                <div class="flex items-center gap-2.5">
                                    <div class="w-6 h-6 rounded-lg bg-emerald-950/70 border border-emerald-900/40 text-emerald-400 flex items-center justify-center shrink-0">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                    </div>
                                    <a href="tel:{{ $outlet->phone }}" class="hover:text-rose-300 font-semibold text-stone-200 transition">
                                        {{ $outlet->phone }}
                                    </a>
                                </div>
                            </div>

                            <!-- In-Store Perks Micro Tags -->
                            <div class="pt-3 border-t border-stone-800/80 flex flex-wrap gap-1.5">
                                <span class="text-[10px] px-2 py-0.5 rounded-md bg-stone-800/70 text-stone-300 font-medium">👠 EU 35–42 Trial</span>
                                <span class="text-[10px] px-2 py-0.5 rounded-md bg-stone-800/70 text-stone-300 font-medium">🔄 On-Spot Exchange</span>
                                <span class="text-[10px] px-2 py-0.5 rounded-md bg-stone-800/70 text-stone-300 font-medium">💳 Card & bKash</span>
                            </div>
                        </div>

                        <!-- Action Footer Buttons -->
                        <div class="p-4 pt-0 grid grid-cols-2 gap-2">
                            @if($outlet->map_link)
                                <a
                                    href="{{ $outlet->map_link }}"
                                    target="_blank"
                                    rel="noopener"
                                    class="inline-flex items-center justify-center gap-1.5 text-xs font-bold text-white bg-rose-900 hover:bg-rose-800 px-3 py-2.5 rounded-xl transition shadow-xs"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <span>Get Directions</span>
                                </a>
                            @endif

                            <a
                                href="tel:{{ $outlet->phone }}"
                                class="inline-flex items-center justify-center gap-1.5 text-xs font-bold text-stone-200 hover:text-white bg-stone-800 hover:bg-stone-700 px-3 py-2.5 rounded-xl transition border border-stone-700/60"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                <span>Call Boutique</span>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- VIP Concierge Strip -->
            <div class="mt-12 sm:mt-16 bg-stone-900/60 border border-stone-800 rounded-3xl p-6 sm:p-7 flex flex-col sm:flex-row items-center justify-between gap-4 text-center sm:text-left">
                <div class="flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-full bg-rose-500/10 border border-rose-500/20 text-rose-400 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-white">Need a Specific Size Reserved Before You Visit?</h4>
                        <p class="text-xs text-stone-400 mt-0.5">Contact our central styling hotline or message us on WhatsApp for complimentary in-store appointment booking.</p>
                    </div>
                </div>

                <a
                    href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $storeSettings['whatsapp_number'] ?? '8801700000000') }}?text={{ urlencode('Hello Heel Bari, I want to reserve a size at your retail boutique.') }}"
                    target="_blank"
                    rel="noopener"
                    class="shrink-0 inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs uppercase tracking-wider transition shadow-md"
                >
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                    <span>WhatsApp Concierge</span>
                </a>
            </div>
        </div>
    </section>

    <!-- 10. VIP FOOTWEAR CLUB & NEWSLETTER -->
    <section class="py-16 sm:py-20 bg-white border-t border-stone-200">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-4">
            <span class="text-xs font-bold text-rose-900 uppercase tracking-widest">VIP Footwear Club</span>
            <h2 class="text-2xl sm:text-4xl font-extrabold text-slate-900 font-serif">
                Be First in Line for Festive Drops & Private Sales
            </h2>
            <p class="text-xs sm:text-sm text-stone-500 max-w-lg mx-auto leading-relaxed">
                Subscribe for early access to seasonal Eid drops, exclusive restock notices for viral Boston clogs, and member-only secret discount vouchers.
            </p>

            <form
                class="pt-4 max-w-md mx-auto flex flex-col sm:flex-row gap-2.5"
                @submit.prevent="notify('✓ Thank you for subscribing to the VIP Footwear Club!')"
            >
                <input
                    type="email"
                    required
                    placeholder="Enter your email address..."
                    class="flex-1 px-5 py-3.5 rounded-full bg-stone-50 border border-stone-300 text-sm focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-rose-800/40 text-slate-800 placeholder:text-stone-400"
                >
                <button
                    type="submit"
                    class="px-8 py-3.5 rounded-full bg-rose-950 hover:bg-rose-900 text-white font-extrabold text-xs uppercase tracking-wider shadow-md transition cursor-pointer"
                >
                    Subscribe
                </button>
            </form>
            <p class="text-[11px] text-stone-400">Zero spam. You can unsubscribe anytime with 1-click.</p>
        </div>
    </section>

@endsection
