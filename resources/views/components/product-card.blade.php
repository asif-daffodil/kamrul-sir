@props([
    'product',
    'showQuickView' => true,
])

@php
    $firstVariant = $product->variants->where('stock', '>', 0)->first() ?? $product->variants->first();
    $availableSizes = $product->available_sizes;
    $availableColors = $product->available_colors;
    $hasSecondImage = !empty($product->secondary_image_url) && $product->secondary_image_url !== $product->image_url;
@endphp

<article
    id="product-card-{{ $product->slug }}"
    class="group relative bg-white rounded-3xl border border-stone-200/90 hover:border-rose-900/30 overflow-hidden shadow-xs hover:shadow-xl transition-all duration-300 flex flex-col justify-between"
    x-data="{
        selectedQuickSize: '{{ $availableSizes[0] ?? '' }}',
        quickAdding: false,
        variantsMap: {{ json_encode($product->variants->mapWithKeys(fn($v) => [(string)$v->size => $v->id])) }},

        async addWithQuickSize(size) {
            this.selectedQuickSize = size;
            const variantId = this.variantsMap[size];
            if (!variantId) return;
            this.quickAdding = true;
            try {
                await quickAdd(variantId);
            } finally {
                this.quickAdding = false;
            }
        }
    }"
>
    <!-- Visual Image Container with Dual Image Hover & Badges -->
    <div class="relative aspect-[4/5] sm:aspect-square overflow-hidden bg-stone-100 select-none">
        <a href="{{ route('product.show', $product->slug) }}" class="block w-full h-full relative" aria-label="View {{ $product->name }}">
            <!-- Primary Image -->
            <img
                src="{{ $product->image_url }}"
                alt="{{ $product->name }} - Heel Bari Footwear"
                width="600"
                height="600"
                loading="lazy"
                class="w-full h-full object-cover transition-all duration-500 {{ $hasSecondImage ? 'group-hover:opacity-0' : 'group-hover:scale-105' }}"
            >

            <!-- Secondary Image (Shown on Hover) -->
            @if($hasSecondImage)
                <img
                    src="{{ $product->secondary_image_url }}"
                    alt="{{ $product->name }} profile view - Heel Bari"
                    width="600"
                    height="600"
                    loading="lazy"
                    class="absolute inset-0 w-full h-full object-cover transition-all duration-500 opacity-0 group-hover:opacity-100 group-hover:scale-105"
                >
            @endif
        </a>

        <!-- Top Badges (Left) -->
        <div class="absolute top-3 left-3 flex flex-col gap-1.5 pointer-events-none z-10">
            @if($product->has_discount)
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-black tracking-wider uppercase bg-rose-900 text-white shadow-xs">
                    -{{ $product->discount_percentage }}% OFF
                </span>
            @endif

            @if($product->is_featured)
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold tracking-wider uppercase bg-[#C5A059] text-white shadow-xs">
                    <span>★</span> BESTSELLER
                </span>
            @elseif(!$product->has_discount)
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-extrabold tracking-wider uppercase bg-slate-900 text-white shadow-xs">
                    NEW
                </span>
            @endif
        </div>

        <!-- Wishlist Button (Right) -->
        <button
            type="button"
            @click.stop.prevent="toggleWishlist({{ $product->id }}, '{{ addslashes($product->name) }}')"
            class="absolute top-3 right-3 z-10 w-8 h-8 rounded-full bg-white/90 hover:bg-white text-stone-600 hover:text-rose-900 backdrop-blur-xs flex items-center justify-center shadow-xs transition transform hover:scale-110 active:scale-95"
            :class="isWishlisted({{ $product->id }}) ? 'text-rose-600 bg-rose-50' : 'text-stone-400'"
            title="Add to Wishlist"
            aria-label="Save {{ $product->name }} to wishlist"
        >
            <svg
                class="w-4 h-4 transition-colors"
                :fill="isWishlisted({{ $product->id }}) ? 'currentColor' : 'none'"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
            </svg>
        </button>

        <!-- Rating Overlay on Image Bottom Left -->
        <div class="absolute bottom-3 left-3 bg-white/95 backdrop-blur-xs text-slate-800 text-[10px] font-bold px-2.5 py-0.5 rounded-full shadow-2xs flex items-center gap-1 pointer-events-none z-10">
            <span class="text-amber-500">★</span>
            <span>{{ $product->average_rating }}</span>
            <span class="text-stone-400 font-normal">({{ $product->reviews_count }})</span>
        </div>

        <!-- Quick View Overlay Action Button (Desktop hover slide-up) -->
        <div class="absolute inset-x-3 bottom-3 hidden sm:flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300 transform translate-y-2 group-hover:translate-y-0 z-20 pointer-events-auto">
            <a
                href="{{ route('product.show', $product->slug) }}"
                class="w-full py-2.5 px-4 rounded-xl bg-white/95 hover:bg-rose-950 hover:text-white text-slate-900 text-xs font-bold tracking-wide uppercase text-center shadow-lg backdrop-blur-xs transition flex items-center justify-center gap-1.5"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                <span>Quick View</span>
            </a>
        </div>
    </div>

    <!-- Product Card Body & Details -->
    <div class="p-4 sm:p-5 flex-1 flex flex-col justify-between">
        <div>
            <!-- Category & Color Swatches Header -->
            <div class="flex items-center justify-between gap-2">
                <span class="text-[10px] font-bold uppercase tracking-wider text-rose-800 truncate">
                    {{ $product->category?->name ?? 'Footwear' }}
                </span>

                <!-- Color Swatches -->
                @if(!empty($availableColors))
                    <div class="flex items-center gap-1 shrink-0" title="{{ count($availableColors) }} available color(s)">
                        @foreach(array_slice($availableColors, 0, 4) as $col)
                            <span
                                class="w-3 h-3 rounded-full border border-stone-300 shadow-2xs"
                                style="background-color: {{ $col['code'] ?? '#111827' }};"
                                title="{{ $col['name'] }}"
                            ></span>
                        @endforeach
                        @if(count($availableColors) > 4)
                            <span class="text-[9px] text-stone-400 font-semibold">+{{ count($availableColors) - 4 }}</span>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Product Title -->
            <h3 class="text-sm sm:text-base font-bold text-slate-900 group-hover:text-rose-950 transition-colors line-clamp-1 mt-1 font-serif">
                <a href="{{ route('product.show', $product->slug) }}">
                    {{ $product->name }}
                </a>
            </h3>

            <!-- Size Chips (Tap to select and quick add) -->
            @if(!empty($availableSizes))
                <div class="mt-2.5 pt-2 border-t border-stone-100">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-[10px] font-semibold text-stone-400">Available Sizes:</span>
                        <span class="text-[9px] text-rose-800 font-medium">EU Fit</span>
                    </div>
                    <div class="flex items-center gap-1 flex-wrap">
                        @foreach($availableSizes as $sz)
                            <button
                                type="button"
                                @click.stop.prevent="addWithQuickSize('{{ $sz }}')"
                                :disabled="quickAdding"
                                class="px-2 py-0.5 rounded-lg text-[10px] font-mono font-semibold border transition active:scale-95"
                                :class="selectedQuickSize === '{{ $sz }}' ? 'bg-rose-950 text-white border-rose-950 shadow-xs' : 'bg-stone-50 hover:bg-stone-100 text-stone-700 border-stone-200'"
                                title="Add size {{ $sz }} to bag"
                            >
                                {{ $sz }}
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Price & Add to Bag Trigger -->
        <div class="mt-4 pt-3 border-t border-stone-100 flex items-center justify-between">
            <div>
                <div class="flex items-baseline gap-2">
                    <span class="text-base sm:text-lg font-extrabold text-slate-950 font-serif">
                        ৳ {{ number_format($product->effective_price) }}
                    </span>
                    @if($product->has_discount)
                        <span class="text-xs text-stone-400 line-through">
                            ৳ {{ number_format($product->original_price) }}
                        </span>
                    @endif
                </div>
                <p class="text-[10px] text-emerald-700 font-semibold flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> COD Available
                </p>
            </div>

            <!-- Instant Bag Button -->
            <button
                type="button"
                @click.stop.prevent="addWithQuickSize(selectedQuickSize)"
                :disabled="quickAdding"
                class="p-2 sm:px-3 sm:py-2 rounded-xl bg-slate-900 hover:bg-rose-950 text-white text-xs font-semibold flex items-center gap-1.5 transition-all shadow-xs active:scale-95 cursor-pointer disabled:opacity-50"
                title="Add to Shopping Bag"
                aria-label="Add {{ $product->name }} to bag"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                <span class="hidden sm:inline" x-text="quickAdding ? 'Adding...' : 'Add'">Add</span>
            </button>
        </div>
    </div>
</article>
