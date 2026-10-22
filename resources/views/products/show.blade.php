@extends('layouts.storefront')

@section('title', ($product->meta_title ?? $product->name) . ' | ' . ($storeSettings['shop_name'] ?? '[BRAND_NAME]'))
@section('meta_description', $product->meta_description ?? $product->short_description ?? ('Buy handcrafted ' . $product->name . ' for women in Bangladesh. Cloud cushion footbed with Cash on Delivery and 7 Days Exchange.'))

@section('og_title', $product->name . ' | ' . ($storeSettings['shop_name'] ?? '[BRAND_NAME]') . ' Handcrafted Footwear')
@section('og_description', $product->short_description ?? 'Handcrafted women footwear with cloud-cushioned comfort in Bangladesh.')
@section('og_image', $product->image_url)
@section('og_type', 'product')
@section('canonical_url', route('product.show', $product->slug))

@section('content')

    <!-- SCHEMA.ORG PRODUCT JSON-LD -->
    <script type="application/ld+json">
    {!! json_encode($productSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
    </script>

    <!-- SCHEMA.ORG BREADCRUMBLIST JSON-LD -->
    <script type="application/ld+json">
    {!! json_encode($breadcrumbSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
    </script>

    <!-- PRODUCT DETAIL CONTAINER (Alpine Reactive State) -->
    <div
        class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8"
        x-data="{
            variants: {{ json_encode($variantsPayload) }},
            selectedColor: '{{ $colors[0]['name'] ?? '' }}',
            selectedSize: '{{ $sizes[0] ?? '' }}',
            currentVariant: null,
            quantity: 1,
            activeImageIndex: 0,
            sizeGuideOpen: false,
            isZoomed: false,
            zoomX: 50,
            zoomY: 50,
            activeTab: 'description',
            isAdding: false,

            init() {
                this.updateCurrentVariant();
            },

            selectColor(color) {
                this.selectedColor = color;
                // If current size isn't available in newly selected color, pick first available size
                const availableForColor = this.variants.filter(v => v.color === color && v.stock > 0);
                if (availableForColor.length > 0 && !availableForColor.some(v => v.size === this.selectedSize)) {
                    this.selectedSize = availableForColor[0].size;
                }
                this.updateCurrentVariant();
            },

            selectSize(size) {
                this.selectedSize = size;
                this.updateCurrentVariant();
            },

            updateCurrentVariant() {
                const match = this.variants.find(v => v.color === this.selectedColor && v.size === this.selectedSize);
                if (match) {
                    this.currentVariant = match;
                    if (this.quantity > match.stock) {
                        this.quantity = Math.max(1, match.stock);
                    }
                } else {
                    // Fallback to first variant matching color
                    this.currentVariant = this.variants.find(v => v.color === this.selectedColor) || this.variants[0];
                }
            },

            isSizeAvailable(size) {
                const match = this.variants.find(v => v.color === this.selectedColor && v.size === size);
                return match && match.stock > 0;
            },

            handleZoom(e) {
                const rect = e.currentTarget.getBoundingClientRect();
                const x = ((e.clientX - rect.left) / rect.width) * 100;
                const y = ((e.clientY - rect.top) / rect.height) * 100;
                this.zoomX = Math.max(0, Math.min(100, x));
                this.zoomY = Math.max(0, Math.min(100, y));
            },

            async addToCart() {
                if (!this.currentVariant || this.currentVariant.stock <= 0) return;
                this.isAdding = true;
                try {
                    const res = await fetch('/api/cart/quick-add', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                        },
                        body: JSON.stringify({
                            variant_id: this.currentVariant.id,
                            quantity: this.quantity
                        })
                    });
                    const data = await res.json();
                    if (data.success) {
                        this.cartCount = data.cart_count;
                        this.notify('✓ Added ' + this.quantity + ' pair(s) to your shopping bag!');
                        window.Livewire?.dispatch('cart-updated');
                        window.Livewire?.dispatch('open-mini-cart');
                    } else {
                        this.notify('⚠ ' + (data.message || 'Could not add to bag.'));
                    }
                } catch (err) {
                    this.notify('⚠ Error adding to cart.');
                } finally {
                    this.isAdding = false;
                }
            }
        }"
    >

        <!-- BREADCRUMB -->
        <nav class="flex items-center text-xs text-stone-500 mb-6 space-x-2 overflow-x-auto no-scrollbar py-1" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="hover:text-rose-950 transition">Home</a>
            <span>/</span>
            <a href="{{ route('products.index') }}" class="hover:text-rose-950 transition">Catalog</a>
            @if($product->category)
                <span>/</span>
                <a href="{{ route('category.show', $product->category->slug) }}" class="hover:text-rose-950 transition">
                    {{ $product->category->name }}
                </a>
            @endif
            <span>/</span>
            <span class="font-bold text-slate-900 truncate max-w-xs">{{ $product->name }}</span>
        </nav>

        <!-- TOP MAIN PRODUCT GRID (Gallery + Details) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start">

            <!-- LEFT: IMAGE GALLERY WITH ZOOM (5 cols on lg) -->
            <div class="lg:col-span-6 space-y-4">

                <!-- Main Display Image with Cursor Magnification Zoom -->
                <div
                    class="relative aspect-square rounded-3xl overflow-hidden bg-stone-100 border border-stone-200 shadow-md group cursor-crosshair select-none"
                    @mouseenter="isZoomed = true"
                    @mouseleave="isZoomed = false"
                    @mousemove="handleZoom($event)"
                >
                    @php
                        $galleryImages = $product->images->isNotEmpty() ? $product->images : collect([
                            (object)['url' => $product->image_url, 'alt_text' => $product->name]
                        ]);
                    @endphp

                    @foreach($galleryImages as $idx => $img)
                        <div
                            class="absolute inset-0 transition-opacity duration-300"
                            x-show="activeImageIndex === {{ $idx }}"
                            x-cloak
                        >
                            <!-- Normal Image -->
                            <img
                                src="{{ $img->url }}"
                                alt="{{ $img->alt_text ?? $product->name }}"
                                width="600"
                                height="600"
                                class="w-full h-full object-cover"
                                :class="{ 'opacity-0': isZoomed }"
                            >

                            <!-- Magnified Zoom Layer -->
                            <div
                                class="absolute inset-0 bg-no-repeat pointer-events-none transition-opacity duration-150"
                                x-show="isZoomed"
                                :style="`background-image: url('{{ $img->url }}'); background-position: ${zoomX}% ${zoomY}%; background-size: 240%;`"
                            ></div>
                        </div>
                    @endforeach

                    <!-- Top Badges on Image -->
                    <div class="absolute top-4 left-4 flex flex-col gap-1.5 pointer-events-none z-10">
                        <template x-if="currentVariant && currentVariant.has_discount">
                            <span class="bg-rose-900 text-white text-xs font-black px-2.5 py-1 rounded-full uppercase tracking-wider shadow-md" x-text="'-' + currentVariant.discount_percentage + '%'">
                            </span>
                        </template>
                        <span class="bg-slate-900/85 backdrop-blur-xs text-white text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider shadow-xs">
                            🇧🇩 Handcrafted BD
                        </span>
                    </div>

                    <!-- Zoom Hint Badge -->
                    <div class="absolute bottom-4 right-4 bg-white/90 backdrop-blur-xs text-slate-700 text-[10px] font-semibold px-2.5 py-1 rounded-full shadow-xs pointer-events-none flex items-center gap-1">
                        <span>🔍 Hover to Zoom</span>
                    </div>
                </div>

                <!-- Thumbnail Strip Carousel -->
                @if($galleryImages->count() > 1)
                    <div class="flex items-center gap-3 overflow-x-auto no-scrollbar py-1">
                        @foreach($galleryImages as $idx => $img)
                            <button
                                type="button"
                                @click="activeImageIndex = {{ $idx }}"
                                class="relative w-20 h-20 rounded-2xl overflow-hidden border-2 transition shrink-0 bg-stone-100"
                                :class="activeImageIndex === {{ $idx }} ? 'border-rose-900 ring-2 ring-rose-900/30 shadow-md scale-102' : 'border-stone-200 opacity-70 hover:opacity-100'"
                                aria-label="View photo {{ $idx + 1 }}"
                            >
                                <img src="{{ $img->url }}" alt="{{ $img->alt_text ?? 'Shoe angle' }}" class="w-full h-full object-cover">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- RIGHT: BUY BOX & PRODUCT DETAILS (6 cols on lg) -->
            <div class="lg:col-span-6 space-y-6">

                <!-- Header Title, Category, Reviews -->
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-widest text-rose-800">
                            {{ $product->category?->name ?? 'Women Footwear' }}
                        </span>
                        <!-- SKU live code -->
                        <span class="text-xs font-mono text-stone-400" x-text="currentVariant ? 'SKU: ' + currentVariant.sku : ''"></span>
                    </div>

                    <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-slate-900 font-serif mt-1">
                        {{ $product->name }}
                    </h1>

                    <!-- Rating summary with link to reviews section -->
                    <div class="mt-2.5 flex items-center gap-3">
                        <div class="flex items-center text-amber-500 text-sm">
                            @for($i = 1; $i <= 5; $i++)
                                <span>{{ $i <= round($averageRating) ? '★' : '☆' }}</span>
                            @endfor
                        </div>
                        <span class="text-xs font-bold text-slate-800">{{ $averageRating }}</span>
                        <span class="text-stone-300">•</span>
                        <a href="#customer-reviews" class="text-xs text-rose-900 font-semibold hover:underline">
                            {{ $totalReviews }} Verified Reviews
                        </a>
                        <span class="text-stone-300">•</span>
                        <span class="text-xs text-emerald-700 font-medium">98% Recommendation</span>
                    </div>
                </div>

                <!-- Price & Discount Badge (Live Reactive to Variant) -->
                <div class="p-4 rounded-2xl bg-stone-50 border border-stone-200/80 flex items-baseline justify-between">
                    <div>
                        <div class="flex items-baseline gap-3">
                            <span class="text-3xl font-black text-slate-950 font-serif" x-text="'৳ ' + Number(currentVariant ? currentVariant.effective_price : {{ $product->effective_price }}).toLocaleString()">
                                ৳ {{ number_format($product->effective_price) }}
                            </span>
                            <span
                                class="text-base text-stone-400 line-through"
                                x-show="currentVariant && currentVariant.has_discount"
                                x-text="'৳ ' + Number(currentVariant ? currentVariant.price : {{ $product->original_price }}).toLocaleString()"
                            >
                                @if($product->has_discount) ৳ {{ number_format($product->original_price) }} @endif
                            </span>
                        </div>
                        <template x-if="currentVariant && currentVariant.has_discount">
                            <p class="text-xs font-bold text-rose-800 mt-1">
                                Save ৳ <span x-text="Number(currentVariant.price - currentVariant.effective_price).toLocaleString()"></span> (<span x-text="currentVariant.discount_percentage"></span>% OFF)
                            </p>
                        </template>
                    </div>

                    <!-- Stock Status Live Badge -->
                    <div class="text-right">
                        <template x-if="currentVariant && currentVariant.stock > 5">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-100/80 text-emerald-800 font-bold text-xs">
                                <span class="w-2 h-2 rounded-full bg-emerald-600 animate-pulse"></span>
                                In Stock
                            </span>
                        </template>
                        <template x-if="currentVariant && currentVariant.stock > 0 && currentVariant.stock <= 5">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-100 text-amber-800 font-bold text-xs">
                                ⚡ Only <span x-text="currentVariant.stock"></span> left!
                            </span>
                        </template>
                        <template x-if="currentVariant && currentVariant.stock === 0">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-rose-100 text-rose-800 font-bold text-xs">
                                ✕ Sold Out
                            </span>
                        </template>
                    </div>
                </div>

                <!-- Short Value Description -->
                @if($product->short_description)
                    <p class="text-sm text-stone-600 leading-relaxed">
                        {{ $product->short_description }}
                    </p>
                @endif

                <!-- COLOR SELECTION -->
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-900 uppercase tracking-wide">
                            Color: <strong class="text-rose-900" x-text="selectedColor">{{ $colors[0]['name'] ?? '' }}</strong>
                        </span>
                    </div>

                    <div class="flex items-center gap-3">
                        @foreach($colors as $col)
                            <button
                                type="button"
                                @click="selectColor('{{ $col['name'] }}')"
                                class="relative w-9 h-9 rounded-full border-2 transition flex items-center justify-center shadow-xs"
                                :class="selectedColor === '{{ $col['name'] }}' ? 'border-rose-900 ring-2 ring-rose-900/30 scale-110' : 'border-stone-300 hover:border-stone-500'"
                                title="{{ $col['name'] }}"
                                aria-label="Select {{ $col['name'] }}"
                            >
                                <span class="w-7 h-7 rounded-full shadow-inner" style="background-color: {{ $col['code'] }};"></span>
                                <span class="sr-only">{{ $col['name'] }}</span>
                                <span x-show="selectedColor === '{{ $col['name'] }}'" class="absolute text-white text-xs drop-shadow-sm font-bold">✓</span>
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- SIZE SELECTION & SIZE GUIDE TRIGGER -->
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-900 uppercase tracking-wide">
                            Size: <strong class="text-rose-900" x-text="'EU ' + selectedSize"></strong>
                        </span>
                        <!-- Size Guide Trigger Button -->
                        <button
                            type="button"
                            @click="sizeGuideOpen = true"
                            class="text-xs text-rose-900 font-bold hover:underline flex items-center gap-1"
                        >
                            <span>📏 Size Guide & Fit Advice</span>
                        </button>
                    </div>

                    <!-- Size Pills -->
                    <div class="grid grid-cols-4 sm:grid-cols-6 gap-2.5">
                        @foreach($sizes as $sz)
                            <button
                                type="button"
                                @click="selectSize('{{ $sz }}')"
                                :disabled="!isSizeAvailable('{{ $sz }}')"
                                class="h-12 rounded-xl text-xs font-mono font-bold flex flex-col items-center justify-center transition border relative"
                                :class="{
                                    'bg-rose-950 text-white border-rose-950 shadow-md': selectedSize === '{{ $sz }}' && isSizeAvailable('{{ $sz }}'),
                                    'bg-white text-slate-800 border-stone-200 hover:border-rose-800': selectedSize !== '{{ $sz }}' && isSizeAvailable('{{ $sz }}'),
                                    'bg-stone-100 text-stone-300 border-stone-200 cursor-not-allowed opacity-50': !isSizeAvailable('{{ $sz }}')
                                }"
                            >
                                <span>EU {{ $sz }}</span>
                                <span class="text-[9px] font-sans font-normal opacity-75" x-text="isSizeAvailable('{{ $sz }}') ? 'In Stock' : 'Out'"></span>
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- QUANTITY & ADD TO BAG ACTIONS -->
                <div class="space-y-3 pt-2">
                    <div class="flex items-center gap-3">
                        <!-- Quantity Counter -->
                        <div class="flex items-center border border-stone-300 rounded-2xl bg-white shadow-2xs h-14 px-2">
                            <button
                                type="button"
                                class="w-9 h-9 text-slate-600 hover:text-rose-950 font-bold text-lg disabled:opacity-30"
                                @click="quantity = Math.max(1, quantity - 1)"
                                :disabled="quantity <= 1"
                                aria-label="Decrease quantity"
                            >-</button>
                            <span class="w-10 text-center font-bold text-sm text-slate-900" x-text="quantity">1</span>
                            <button
                                type="button"
                                class="w-9 h-9 text-slate-600 hover:text-rose-950 font-bold text-lg disabled:opacity-30"
                                @click="quantity = Math.min(currentVariant?.stock || 10, quantity + 1)"
                                :disabled="currentVariant && quantity >= currentVariant.stock"
                                aria-label="Increase quantity"
                            >+</button>
                        </div>

                        <!-- Add to Bag Primary Button -->
                        <button
                            type="button"
                            @click="addToCart()"
                            :disabled="isAdding || !currentVariant || currentVariant.stock === 0"
                            class="flex-1 h-14 rounded-2xl bg-rose-900 hover:bg-rose-800 text-white font-extrabold text-sm sm:text-base tracking-wider uppercase shadow-xl hover:shadow-rose-900/30 transition-all flex items-center justify-center gap-2.5 disabled:opacity-50 disabled:cursor-not-allowed active:scale-98"
                        >
                            <span x-show="!isAdding">🛍️ Add to Shopping Bag</span>
                            <span x-show="isAdding" class="flex items-center gap-2">
                                <span class="animate-spin inline-block">⏳</span> Adding to bag...
                            </span>
                        </button>
                    </div>

                    <!-- Direct WhatsApp Order Button -->
                    <a
                        :href="`https://wa.me/{{ preg_replace('/[^0-9]/', '', $storeSettings['whatsapp_number'] ?? '+8801700000000') }}?text=${encodeURIComponent('Hi [BRAND_NAME]! I want to order ' + '{{ $product->name }}' + ' (Color: ' + selectedColor + ', Size: EU ' + selectedSize + ') for BDT ' + (currentVariant ? currentVariant.effective_price : '{{ $product->effective_price }}'))}`"
                        target="_blank"
                        rel="noopener"
                        class="w-full h-12 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs uppercase tracking-wider flex items-center justify-center gap-2 transition shadow-sm"
                    >
                        <span>💬 Order Instantly via WhatsApp</span>
                    </a>
                </div>

                <!-- VALUE PROMISES & LOCALIZED BANGLADESH SHIPPING DETAILS -->
                <div class="border border-stone-200 rounded-2xl p-4 bg-stone-50/70 space-y-3 text-xs text-stone-600">
                    <div class="flex items-start gap-3">
                        <span class="text-base text-rose-800 mt-0.5">🚚</span>
                        <div>
                            <p class="font-bold text-slate-900">Fast Home Delivery Across Bangladesh</p>
                            <p class="text-stone-500">Dhaka: 24–48 hours (৳70) • Outside Dhaka: 3–5 business days (৳130). <strong>Free on orders ৳3,000+</strong>.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 pt-2 border-t border-stone-200/60">
                        <span class="text-base text-amber-700 mt-0.5">🔄</span>
                        <div>
                            <p class="font-bold text-slate-900">7 Days Hassle-Free Size Exchange</p>
                            <p class="text-stone-500">Wrong size? We will swap it at your doorstep or in any of our 6 physical boutiques.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 pt-2 border-t border-stone-200/60">
                        <span class="text-base text-emerald-700 mt-0.5">💵</span>
                        <div>
                            <p class="font-bold text-slate-900">Cash on Delivery & bKash Available</p>
                            <p class="text-stone-500">Pay conveniently after inspecting the shoes at your home.</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- PRODUCT TABS (Description, Specs, Care) -->
        <div class="mt-16 pt-8 border-t border-stone-200">
            <div class="flex items-center gap-4 sm:gap-8 border-b border-stone-200 pb-px text-sm font-bold">
                <button
                    type="button"
                    @click="activeTab = 'description'"
                    class="pb-3 border-b-2 transition"
                    :class="activeTab === 'description' ? 'border-rose-900 text-rose-950' : 'border-transparent text-stone-500 hover:text-slate-800'"
                >
                    Product Description
                </button>
                <button
                    type="button"
                    @click="activeTab = 'materials'"
                    class="pb-3 border-b-2 transition"
                    :class="activeTab === 'materials' ? 'border-rose-900 text-rose-950' : 'border-transparent text-stone-500 hover:text-slate-800'"
                >
                    Materials & Fit Specs
                </button>
                <button
                    type="button"
                    @click="activeTab = 'care'"
                    class="pb-3 border-b-2 transition"
                    :class="activeTab === 'care' ? 'border-rose-900 text-rose-950' : 'border-transparent text-stone-500 hover:text-slate-800'"
                >
                    Care Instructions
                </button>
            </div>

            <div class="py-6 text-sm text-stone-600 leading-relaxed max-w-4xl">
                <!-- Tab 1: Description -->
                <div x-show="activeTab === 'description'" class="space-y-4">
                    <p>{{ $product->description }}</p>
                    <p>
                        Designed with high-density multi-layered latex cushion foam and ergonomic arch support, our shoes are crafted to give you painless elevation whether you are attending a traditional Bangladeshi wedding ceremony or working through an 8-hour day.
                    </p>
                </div>

                <!-- Tab 2: Materials & Specs -->
                <div x-show="activeTab === 'materials'" class="space-y-3" x-cloak>
                    <ul class="list-disc pl-5 space-y-1.5">
                        <li><strong>Upper:</strong> Handcrafted premium velvet / vegetable-tanned genuine leather.</li>
                        <li><strong>Insole:</strong> Ergonomic orthopedic dual-layer memory foam with sweat-wicking lining.</li>
                        <li><strong>Outsole:</strong> Flexible slip-resistant TPR rubber formulated for Dhaka tile and asphalt surfaces.</li>
                        <li><strong>Heel / Sole Height:</strong> Balanced posture elevation designed to alleviate lower back strain.</li>
                        <li><strong>Origin:</strong> 100% Handcrafted by master artisans in Dhaka, Bangladesh.</li>
                    </ul>
                </div>

                <!-- Tab 3: Care -->
                <div x-show="activeTab === 'care'" class="space-y-3" x-cloak>
                    <p>To preserve the luxurious texture and longevity of your footwear:</p>
                    <ul class="list-disc pl-5 space-y-1.5">
                        <li>Use a soft horsehair brush to gently remove dust after every wear.</li>
                        <li>Avoid soaking in water; spot-clean velvet and leather uppers with a damp microfiber cloth.</li>
                        <li>Store in the provided breathable cotton dust bag away from direct tropical sunlight.</li>
                        <li>Insert shoe trees or tissue paper when storing for festive seasons to retain the silhouette.</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- APPROVED CUSTOMER REVIEWS & RATING DISTRIBUTION -->
        <section id="customer-reviews" class="mt-16 pt-12 border-t border-stone-200">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-8 gap-4">
                <div>
                    <span class="text-xs font-bold text-rose-900 uppercase tracking-widest">Customer Feedback</span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 font-serif mt-1">
                        Verified Customer Reviews
                    </h2>
                </div>
                <div class="text-xs sm:text-sm text-stone-500">
                    Showing only approved authentic reviews
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

                <!-- Overall Rating Score Box (4 cols) -->
                <div class="lg:col-span-4 bg-stone-50 p-6 rounded-3xl border border-stone-200 text-center sm:text-left space-y-4">
                    <div class="flex items-center justify-center sm:justify-start gap-4">
                        <span class="text-5xl font-black text-slate-900 font-serif">{{ $averageRating }}</span>
                        <div>
                            <div class="flex items-center text-amber-500 text-lg">
                                @for($i = 1; $i <= 5; $i++)
                                    <span>{{ $i <= round($averageRating) ? '★' : '☆' }}</span>
                                @endfor
                            </div>
                            <p class="text-xs text-stone-500 mt-0.5">Based on {{ $totalReviews }} reviews</p>
                        </div>
                    </div>

                    <!-- Star Distribution Bars -->
                    <div class="space-y-2 pt-2 border-t border-stone-200">
                        @foreach($ratingDistribution as $star => $data)
                            <div class="flex items-center gap-2 text-xs">
                                <span class="w-7 font-bold text-slate-700">{{ $star }} ★</span>
                                <div class="flex-1 h-2 rounded-full bg-stone-200 overflow-hidden">
                                    <div class="h-full bg-amber-500 rounded-full" style="width: {{ $data['percentage'] }}%;"></div>
                                </div>
                                <span class="w-8 text-right text-stone-400 font-mono">{{ $data['count'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Reviews Cards List (8 cols) -->
                <div class="lg:col-span-8 space-y-4">
                    @forelse($product->approvedReviews as $rev)
                        <div class="p-5 rounded-2xl bg-white border border-stone-200 shadow-2xs space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-full bg-rose-100 text-rose-800 font-bold text-xs flex items-center justify-center">
                                        {{ substr($rev->user?->name ?? 'Shopper', 0, 1) }}
                                    </div>
                                    <div>
                                        <h4 class="text-xs font-bold text-slate-900">{{ $rev->user?->name ?? 'Verified Customer' }}</h4>
                                        <span class="text-[10px] text-emerald-700 font-medium">✓ Verified Purchase</span>
                                    </div>
                                </div>

                                <div class="flex items-center text-amber-500 text-xs">
                                    @for($i = 1; $i <= 5; $i++)
                                        <span>{{ $i <= $rev->rating ? '★' : '☆' }}</span>
                                    @endfor
                                </div>
                            </div>

                            <p class="text-xs sm:text-sm text-stone-700 leading-relaxed italic">
                                "{{ $rev->comment }}"
                            </p>
                            <p class="text-[10px] text-stone-400">Reviewed in Bangladesh • {{ $rev->created_at->diffForHumans() }}</p>
                        </div>
                    @empty
                        <div class="p-8 rounded-2xl bg-stone-50 border border-stone-200 text-center">
                            <p class="text-xs sm:text-sm text-stone-500">Be the first to review this handcrafted pair!</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        <!-- RELATED PRODUCTS (4 Items) -->
        @if($relatedProducts->isNotEmpty())
            <section class="mt-20 pt-12 border-t border-stone-200">
                <div class="flex items-end justify-between mb-8">
                    <div>
                        <span class="text-xs font-bold text-rose-900 uppercase tracking-widest">Complete Your Look</span>
                        <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 font-serif mt-1">
                            You May Also Love
                        </h2>
                    </div>
                    <a href="{{ route('products.index') }}" class="text-xs sm:text-sm font-bold text-rose-900 hover:underline">
                        View All Footwear →
                    </a>
                </div>

                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                    @foreach($relatedProducts as $rel)
                        <article class="group bg-white rounded-2xl border border-stone-200 hover:border-rose-900/40 overflow-hidden shadow-xs hover:shadow-xl transition-all duration-300 flex flex-col justify-between">
                            <div class="relative aspect-square overflow-hidden bg-stone-100">
                                <a href="{{ route('product.show', $rel->slug) }}">
                                    <img
                                        src="{{ $rel->image_url }}"
                                        alt="{{ $rel->name }}"
                                        width="600"
                                        height="600"
                                        loading="lazy"
                                        class="w-full h-full object-cover group-hover:scale-106 transition-transform duration-500"
                                    >
                                </a>
                                @if($rel->has_discount)
                                    <div class="absolute top-3 left-3 bg-rose-800 text-white text-[10px] font-extrabold px-2 py-0.5 rounded-full uppercase">
                                        -{{ $rel->discount_percentage }}%
                                    </div>
                                @endif
                            </div>

                            <div class="p-4 flex-1 flex flex-col justify-between">
                                <div>
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-rose-800">{{ $rel->category?->name }}</span>
                                    <h3 class="text-xs sm:text-sm font-bold text-slate-900 group-hover:text-rose-900 transition-colors line-clamp-1 mt-0.5">
                                        <a href="{{ route('product.show', $rel->slug) }}">{{ $rel->name }}</a>
                                    </h3>
                                </div>
                                <div class="mt-4 pt-3 border-t border-stone-100 flex items-center justify-between">
                                    <span class="text-sm font-extrabold text-slate-900">৳ {{ number_format($rel->effective_price) }}</span>
                                    <a
                                        href="{{ route('product.show', $rel->slug) }}"
                                        class="px-2.5 py-1 rounded-lg bg-stone-100 hover:bg-rose-900 hover:text-white text-xs font-semibold transition"
                                    >
                                        View
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        <!-- SIZE GUIDE MODAL -->
        <div
            class="fixed inset-0 z-50 overflow-y-auto p-4 sm:p-6 md:p-12 flex items-center justify-center"
            x-show="sizeGuideOpen"
            x-cloak
            role="dialog"
            aria-modal="true"
        >
            <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs transition-opacity" @click="sizeGuideOpen = false"></div>

            <div
                class="relative max-w-2xl w-full bg-white rounded-3xl p-6 sm:p-8 shadow-2xl border border-stone-200 z-10 space-y-6 max-h-[90vh] overflow-y-auto"
                x-show="sizeGuideOpen"
                x-transition
            >
                <div class="flex items-center justify-between pb-4 border-b border-stone-200">
                    <div class="flex items-center gap-2">
                        <span class="text-xl">📏</span>
                        <h3 class="text-lg sm:text-xl font-extrabold text-slate-900 font-serif">Women's Size Guide & Fit Advice</h3>
                    </div>
                    <button type="button" @click="sizeGuideOpen = false" class="text-stone-400 hover:text-slate-800 text-lg">✕</button>
                </div>

                <p class="text-xs text-stone-600">
                    All our shoes are handcrafted to Bangladeshi and European standard footwear dimensions with extra cushioning. Follow our measurement chart below to find your ideal fit.
                </p>

                <!-- Size Table -->
                <div class="overflow-x-auto rounded-2xl border border-stone-200">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-stone-100 text-slate-700 uppercase font-bold text-[10px] tracking-wider border-b border-stone-200">
                            <tr>
                                <th class="py-3 px-3">EU Size</th>
                                <th class="py-3 px-3">Foot Length (cm)</th>
                                <th class="py-3 px-3">Foot Length (in)</th>
                                <th class="py-3 px-3">UK / BD Local</th>
                                <th class="py-3 px-3">US Women</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 font-mono text-slate-800">
                            <tr><td class="py-2.5 px-3 font-bold text-rose-950">35</td><td class="py-2.5 px-3">22.5 cm</td><td class="py-2.5 px-3">8.85"</td><td class="py-2.5 px-3">2.5</td><td class="py-2.5 px-3">4.5</td></tr>
                            <tr class="bg-stone-50/50"><td class="py-2.5 px-3 font-bold text-rose-950">36</td><td class="py-2.5 px-3">23.0 cm</td><td class="py-2.5 px-3">9.05"</td><td class="py-2.5 px-3">3.0</td><td class="py-2.5 px-3">5.5</td></tr>
                            <tr><td class="py-2.5 px-3 font-bold text-rose-950">37</td><td class="py-2.5 px-3">23.5 cm</td><td class="py-2.5 px-3">9.25"</td><td class="py-2.5 px-3">4.0</td><td class="py-2.5 px-3">6.5</td></tr>
                            <tr class="bg-stone-50/50"><td class="py-2.5 px-3 font-bold text-rose-950">38</td><td class="py-2.5 px-3">24.0 cm</td><td class="py-2.5 px-3">9.45"</td><td class="py-2.5 px-3">5.0</td><td class="py-2.5 px-3">7.5</td></tr>
                            <tr><td class="py-2.5 px-3 font-bold text-rose-950">39</td><td class="py-2.5 px-3">24.5 cm</td><td class="py-2.5 px-3">9.65"</td><td class="py-2.5 px-3">6.0</td><td class="py-2.5 px-3">8.5</td></tr>
                            <tr class="bg-stone-50/50"><td class="py-2.5 px-3 font-bold text-rose-950">40</td><td class="py-2.5 px-3">25.0 cm</td><td class="py-2.5 px-3">9.85"</td><td class="py-2.5 px-3">7.0</td><td class="py-2.5 px-3">9.5</td></tr>
                            <tr><td class="py-2.5 px-3 font-bold text-rose-950">41</td><td class="py-2.5 px-3">25.5 cm</td><td class="py-2.5 px-3">10.05"</td><td class="py-2.5 px-3">7.5</td><td class="py-2.5 px-3">10.0</td></tr>
                            <tr class="bg-stone-50/50"><td class="py-2.5 px-3 font-bold text-rose-950">42</td><td class="py-2.5 px-3">26.0 cm</td><td class="py-2.5 px-3">10.25"</td><td class="py-2.5 px-3">8.0</td><td class="py-2.5 px-3">10.5</td></tr>
                        </tbody>
                    </table>
                </div>

                <!-- Expert Fit Tips -->
                <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-xs text-rose-950 space-y-1.5">
                    <p class="font-bold">💡 Bangladesh Footwear Fit Tips:</p>
                    <p>• <strong>Wide Feet:</strong> If you have broader feet or high insteps, we recommend going <strong>one EU size up</strong> for pointed pumps and closed-toe mules.</p>
                    <p>• <strong>Free Doorstep Size Exchange:</strong> In case the size doesn't feel like a glove, our courier will swap it at your doorstep within 7 days.</p>
                </div>

                <button
                    type="button"
                    @click="sizeGuideOpen = false"
                    class="w-full py-3 rounded-2xl bg-slate-900 text-white font-bold text-xs uppercase tracking-wider hover:bg-slate-800 transition"
                >
                    Got It, Continue Shopping
                </button>
            </div>
        </div>

    </div>

@endsection
