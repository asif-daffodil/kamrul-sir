<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="{ mobileFilterOpen: false }">

    <!-- 1. BREADCRUMBS -->
    <nav class="flex items-center text-xs text-stone-500 mb-6 space-x-2" aria-label="Breadcrumb">
        <a href="{{ route('home') }}" class="hover:text-rose-950 transition">Home</a>
        <span>/</span>
        <a href="{{ route('products.index') }}" class="hover:text-rose-950 transition">Catalog</a>
        @if($this->currentCategory)
            @if($this->currentCategory->parent)
                <span>/</span>
                <a href="{{ route('category.show', $this->currentCategory->parent->slug) }}" class="hover:text-rose-950 transition">
                    {{ $this->currentCategory->parent->name }}
                </a>
            @endif
            <span>/</span>
            <span class="font-bold text-slate-900">{{ $this->currentCategory->name }}</span>
        @endif
    </nav>

    <!-- 2. HEADER & LIVE SEARCH BAR -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 pb-6 border-b border-stone-200">
        <div>
            <div class="inline-flex items-center gap-2 text-rose-800 text-xs font-bold uppercase tracking-widest mb-1">
                <span>Handcrafted in Bangladesh</span>
            </div>
            <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-900 font-serif">
                @if($this->currentCategory)
                    {{ $this->currentCategory->name }}
                @elseif(!empty($search))
                    Results for "{{ $search }}"
                @else
                    Women's Footwear Catalog
                @endif
            </h1>
            <p class="text-xs sm:text-sm text-stone-500 mt-1 max-w-xl">
                @if($this->currentCategory && $this->currentCategory->description)
                    {{ $this->currentCategory->description }}
                @else
                    Discover cloud-cushioned block heels, ergonomic clogs, braided slides and festive Kolhapuris.
                @endif
            </p>
        </div>

        <!-- Livewire Instant Search Input with Suggestions Dropdown -->
        <div class="relative w-full md:w-80">
            <div class="relative flex items-center">
                <svg class="pointer-events-none absolute left-3.5 h-4 w-4 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search within listing..."
                    class="h-11 w-full rounded-full bg-stone-100 pl-10 pr-9 text-xs sm:text-sm text-slate-900 placeholder:text-stone-400 focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-rose-800/40 border border-stone-200 transition"
                >
                @if(!empty($search))
                    <button
                        type="button"
                        wire:click="$set('search', '')"
                        class="absolute right-3 text-stone-400 hover:text-slate-700"
                        title="Clear search"
                    >
                        ✕
                    </button>
                @endif
            </div>

            <!-- Live Search Suggestions Dropdown -->
            @if(!empty($this->suggestions))
                <div class="absolute left-0 right-0 top-full mt-1 bg-white rounded-2xl shadow-xl border border-stone-200 p-2 z-50">
                    <p class="text-[10px] font-bold text-stone-400 uppercase tracking-wider px-2 py-1">Quick Matches</p>
                    <div class="divide-y divide-stone-100">
                        @foreach($this->suggestions as $sug)
                            <div
                                wire:click="selectSuggestion('{{ addslashes($sug['name']) }}')"
                                class="flex items-center gap-3 p-2 rounded-xl hover:bg-rose-50 cursor-pointer transition"
                            >
                                <img src="{{ $sug['image_url'] }}" alt="{{ $sug['name'] }}" class="w-10 h-10 rounded-lg object-cover bg-stone-100 border border-stone-200">
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-bold text-slate-900 truncate">{{ $sug['name'] }}</p>
                                    <p class="text-[10px] text-stone-500">{{ $sug['category'] }} • ৳ {{ number_format($sug['price']) }}</p>
                                </div>
                                <span class="text-[10px] text-rose-800 font-semibold bg-rose-100/70 px-2 py-0.5 rounded-full">Select</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- 3. SUBCATEGORY PILLS (If viewing parent category) -->
    @if($this->currentCategory && $this->currentCategory->children->isNotEmpty())
        <div class="py-4 border-b border-stone-200/80 flex items-center gap-2 overflow-x-auto no-scrollbar">
            <span class="text-xs font-bold text-stone-400 whitespace-nowrap">Subcategories:</span>
            <button
                type="button"
                wire:click="selectCategory('{{ $this->currentCategory->slug }}')"
                class="px-3 py-1.5 rounded-full text-xs font-bold transition whitespace-nowrap {{ $categorySlug === $this->currentCategory->slug ? 'bg-rose-950 text-white' : 'bg-stone-100 text-stone-700 hover:bg-stone-200' }}"
            >
                All {{ $this->currentCategory->name }}
            </button>
            @foreach($this->currentCategory->children as $child)
                <button
                    type="button"
                    wire:click="selectCategory('{{ $child->slug }}')"
                    class="px-3 py-1.5 rounded-full text-xs font-medium transition whitespace-nowrap {{ $categorySlug === $child->slug ? 'bg-rose-950 text-white' : 'bg-stone-100 text-stone-700 hover:bg-stone-200' }}"
                >
                    {{ $child->name }}
                </button>
            @endforeach
        </div>
    @endif

    <!-- 4. ACTIVE FILTER CHIPS -->
    @if($this->activeFiltersCount > 0)
        <div class="py-3 flex flex-wrap items-center gap-2 text-xs">
            <span class="font-bold text-stone-400">Active Filters:</span>

            @if(!empty($search))
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-rose-50 text-rose-900 border border-rose-200 font-medium">
                    <span>Search: "{{ $search }}"</span>
                    <button type="button" wire:click="$set('search', '')" class="hover:text-rose-950">✕</button>
                </span>
            @endif

            @foreach($selectedSizes as $sz)
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-stone-100 text-slate-800 border border-stone-200 font-mono font-medium">
                    <span>Size EU {{ $sz }}</span>
                    <button type="button" wire:click="toggleSize('{{ $sz }}')" class="hover:text-rose-900">✕</button>
                </span>
            @endforeach

            @foreach($selectedColors as $col)
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-stone-100 text-slate-800 border border-stone-200 font-medium">
                    <span>{{ $col }}</span>
                    <button type="button" wire:click="toggleColor('{{ $col }}')" class="hover:text-rose-900">✕</button>
                </span>
            @endforeach

            @if($minPrice !== null || $maxPrice !== null)
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-50 text-amber-900 border border-amber-200 font-medium">
                    <span>৳{{ number_format($minPrice ?? 0) }} - ৳{{ number_format($maxPrice ?? 5000) }}</span>
                    <button type="button" wire:click="setPriceRange(null, null)" class="hover:text-amber-950">✕</button>
                </span>
            @endif

            @if($inStockOnly)
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 text-emerald-900 border border-emerald-200 font-medium">
                    <span>In Stock Only</span>
                    <button type="button" wire:click="$set('inStockOnly', false)" class="hover:text-emerald-950">✕</button>
                </span>
            @endif

            <button
                type="button"
                wire:click="clearFilters"
                class="text-xs text-rose-900 font-bold hover:underline ml-2"
            >
                Clear All
            </button>
        </div>
    @endif

    <!-- 5. MAIN CONTENT LAYOUT (Sidebar Filters + Products Grid) -->
    <div class="mt-6 grid grid-cols-1 lg:grid-cols-4 gap-8 items-start">

        <!-- DESKTOP SIDEBAR FILTERS (Hidden on mobile) -->
        <aside class="hidden lg:block lg:col-span-1 bg-white rounded-2xl p-6 border border-stone-200 space-y-6 shadow-xs sticky top-24">
            <div class="flex items-center justify-between pb-3 border-b border-stone-100">
                <h3 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider">Filters</h3>
                @if($this->activeFiltersCount > 0)
                    <button type="button" wire:click="clearFilters" class="text-xs text-rose-800 font-bold hover:underline">
                        Reset ({{ $this->activeFiltersCount }})
                    </button>
                @endif
            </div>

            <!-- Category Tree Filter -->
            <div class="space-y-2">
                <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wide">Category</h4>
                <div class="space-y-1 max-h-56 overflow-y-auto pr-1">
                    <button
                        type="button"
                        wire:click="selectCategory(null)"
                        class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs font-medium flex items-center justify-between transition {{ empty($categorySlug) ? 'bg-rose-950 text-white font-bold' : 'text-slate-700 hover:bg-stone-100' }}"
                    >
                        <span>All Categories</span>
                    </button>
                    @foreach($this->categories as $c)
                        <button
                            type="button"
                            wire:click="selectCategory('{{ $c->slug }}')"
                            class="w-full text-left px-2.5 py-1.5 rounded-lg text-xs font-medium flex items-center justify-between transition {{ $categorySlug === $c->slug ? 'bg-rose-950 text-white font-bold' : 'text-slate-700 hover:bg-stone-100' }}"
                        >
                            <span>{{ $c->name }}</span>
                            <span class="text-[10px] {{ $categorySlug === $c->slug ? 'text-rose-200' : 'text-stone-400' }}">({{ $c->products_count }})</span>
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Size Filter (EU 35-42) -->
            <div class="pt-4 border-t border-stone-100 space-y-2.5">
                <div class="flex items-center justify-between">
                    <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wide">Size (EU)</h4>
                    <span class="text-[10px] text-stone-400 font-medium">Bangladesh Standard</span>
                </div>
                <div class="grid grid-cols-4 gap-2">
                    @foreach($allSizes as $sz)
                        @php $isSelected = in_array($sz, $selectedSizes); @endphp
                        <button
                            type="button"
                            wire:click="toggleSize('{{ $sz }}')"
                            class="h-9 rounded-xl text-xs font-mono font-bold flex items-center justify-center transition border {{ $isSelected ? 'bg-rose-950 text-white border-rose-950 shadow-xs' : 'bg-stone-50 text-slate-700 border-stone-200 hover:border-stone-400' }}"
                        >
                            {{ $sz }}
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Color Filter Swatches -->
            <div class="pt-4 border-t border-stone-100 space-y-2.5">
                <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wide">Color</h4>
                <div class="space-y-1.5">
                    @foreach($allColors as $c)
                        @php $isSelected = in_array($c['name'], $selectedColors); @endphp
                        <button
                            type="button"
                            wire:click="toggleColor('{{ $c['name'] }}')"
                            class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg text-xs font-medium transition text-left {{ $isSelected ? 'bg-rose-50 text-rose-950 font-bold border border-rose-200' : 'text-slate-700 hover:bg-stone-50' }}"
                        >
                            <span class="w-3.5 h-3.5 rounded-full border border-stone-300 shadow-2xs shrink-0" style="background-color: {{ $c['code'] }};"></span>
                            <span class="flex-1 truncate">{{ $c['name'] }}</span>
                            @if($isSelected)
                                <span class="text-rose-800 font-bold">✓</span>
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Price Range Filter -->
            <div class="pt-4 border-t border-stone-100 space-y-3">
                <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wide">Price Range (BDT)</h4>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="text-[10px] text-stone-400 font-medium">Min (৳)</label>
                        <input
                            type="number"
                            wire:model.live.debounce.400ms="minPrice"
                            placeholder="900"
                            class="w-full text-xs p-2 rounded-lg bg-stone-50 border border-stone-200 focus:bg-white"
                        >
                    </div>
                    <div>
                        <label class="text-[10px] text-stone-400 font-medium">Max (৳)</label>
                        <input
                            type="number"
                            wire:model.live.debounce.400ms="maxPrice"
                            placeholder="5000"
                            class="w-full text-xs p-2 rounded-lg bg-stone-50 border border-stone-200 focus:bg-white"
                        >
                    </div>
                </div>

                <!-- Price Presets -->
                <div class="flex flex-wrap gap-1.5 pt-1">
                    <button type="button" wire:click="setPriceRange(0, 1500)" class="text-[10px] px-2 py-1 rounded bg-stone-100 hover:bg-rose-50 hover:text-rose-900 transition">Under ৳1,500</button>
                    <button type="button" wire:click="setPriceRange(1500, 2500)" class="text-[10px] px-2 py-1 rounded bg-stone-100 hover:bg-rose-50 hover:text-rose-900 transition">৳1,500 - ৳2,500</button>
                    <button type="button" wire:click="setPriceRange(2500, 4000)" class="text-[10px] px-2 py-1 rounded bg-stone-100 hover:bg-rose-50 hover:text-rose-900 transition">৳2,500 - ৳4,000</button>
                </div>
            </div>

            <!-- In-Stock Only Switch -->
            <div class="pt-4 border-t border-stone-100 flex items-center justify-between">
                <div>
                    <h4 class="text-xs font-bold text-slate-800">In-Stock Only</h4>
                    <p class="text-[10px] text-stone-400">Hide sold out pairs</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" wire:model.live="inStockOnly" class="sr-only peer">
                    <div class="w-9 h-5 bg-stone-200 peer-focus:outline-hidden rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-stone-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-rose-900"></div>
                </label>
            </div>
        </aside>

        <!-- PRODUCT GRID & CONTROLS (3 columns on desktop, 2 on mobile) -->
        <main class="lg:col-span-3">

            <!-- Controls Bar (Results Counter, Mobile Filter Trigger, Sort Dropdown) -->
            <div class="bg-white rounded-2xl p-4 border border-stone-200 shadow-2xs mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center justify-between sm:justify-start gap-4">
                    <!-- Mobile Filter Drawer Toggle Button -->
                    <button
                        type="button"
                        class="lg:hidden px-4 py-2 rounded-xl bg-stone-100 hover:bg-stone-200 text-xs font-bold text-slate-800 flex items-center gap-2 transition"
                        @click="mobileFilterOpen = true"
                    >
                        <svg class="w-4 h-4 text-rose-800" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                        <span>Filters</span>
                        @if($this->activeFiltersCount > 0)
                            <span class="bg-rose-900 text-white text-[10px] w-4 h-4 rounded-full flex items-center justify-center font-bold">
                                {{ $this->activeFiltersCount }}
                            </span>
                        @endif
                    </button>

                    <p class="text-xs text-stone-500">
                        Showing <strong class="text-slate-900">{{ $products->firstItem() ?? 0 }} - {{ $products->lastItem() ?? 0 }}</strong> of <strong class="text-slate-900">{{ $products->total() }}</strong> pairs
                    </p>
                </div>

                <!-- Sort Mode Dropdown -->
                <div class="flex items-center gap-2">
                    <label for="sort-select" class="text-xs font-bold text-stone-500 whitespace-nowrap">Sort By:</label>
                    <select
                        id="sort-select"
                        wire:model.live="sort"
                        class="text-xs font-semibold py-2 pl-3 pr-8 rounded-xl bg-stone-50 border border-stone-200 text-slate-800 focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-rose-800/40"
                    >
                        <option value="latest">New Arrivals</option>
                        <option value="popular">Best Selling / Featured</option>
                        <option value="price_asc">Price: Low to High</option>
                        <option value="price_desc">Price: High to Low</option>
                        <option value="rating">Top Customer Rated</option>
                    </select>
                </div>
            </div>

            <!-- Products Grid -->
            @if($products->isNotEmpty())
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4 sm:gap-6">
                    @foreach($products as $product)
                        <x-product-card :product="$product" />
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-10">
                    {{ $products->links() }}
                </div>
            @else
                <!-- Empty State -->
                <div class="bg-white rounded-3xl p-12 text-center border border-stone-200">
                    <span class="text-4xl">👠</span>
                    <h3 class="text-lg font-bold text-slate-900 mt-3 font-serif">No footwear matches your filters</h3>
                    <p class="text-xs sm:text-sm text-stone-500 mt-1 max-w-sm mx-auto">
                        Try adjusting your size, color, or price range filters to discover other handcrafted styles.
                    </p>
                    <button
                        type="button"
                        wire:click="clearFilters"
                        class="mt-5 px-6 py-2.5 rounded-full bg-rose-900 text-white text-xs font-bold uppercase tracking-wider shadow-sm hover:bg-rose-800 transition"
                    >
                        Reset All Filters
                    </button>
                </div>
            @endif
        </main>
    </div>

    <!-- 6. MOBILE FILTER SLIDE-OVER DRAWER -->
    <div
        class="relative z-50 lg:hidden"
        x-show="mobileFilterOpen"
        x-cloak
    >
        <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs transition-opacity" @click="mobileFilterOpen = false"></div>

        <div
            class="fixed inset-y-0 right-0 max-w-xs w-full bg-white shadow-2xl flex flex-col z-50 overflow-y-auto"
            x-show="mobileFilterOpen"
            x-transition:enter="transform transition ease-out duration-300"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transform transition ease-in duration-200"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
        >
            <div class="p-4 border-b border-stone-200 flex items-center justify-between bg-stone-900 text-white">
                <span class="font-bold text-sm uppercase tracking-wider">Filter Footwear</span>
                <button type="button" @click="mobileFilterOpen = false" class="text-stone-300 hover:text-white">✕</button>
            </div>

            <div class="p-4 space-y-6 flex-1">
                <!-- Size Filter -->
                <div>
                    <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wide mb-2">Sizes (EU)</h4>
                    <div class="grid grid-cols-4 gap-2">
                        @foreach($allSizes as $sz)
                            @php $isSelected = in_array($sz, $selectedSizes); @endphp
                            <button
                                type="button"
                                wire:click="toggleSize('{{ $sz }}')"
                                class="h-9 rounded-xl text-xs font-mono font-bold flex items-center justify-center transition border {{ $isSelected ? 'bg-rose-950 text-white border-rose-950' : 'bg-stone-50 text-slate-700 border-stone-200' }}"
                            >
                                {{ $sz }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- Color Filter -->
                <div>
                    <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wide mb-2">Colors</h4>
                    <div class="space-y-1.5">
                        @foreach($allColors as $c)
                            @php $isSelected = in_array($c['name'], $selectedColors); @endphp
                            <button
                                type="button"
                                wire:click="toggleColor('{{ $c['name'] }}')"
                                class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg text-xs font-medium transition text-left {{ $isSelected ? 'bg-rose-50 text-rose-950 font-bold border border-rose-200' : 'text-slate-700 hover:bg-stone-50' }}"
                            >
                                <span class="w-3.5 h-3.5 rounded-full border border-stone-300 shrink-0" style="background-color: {{ $c['code'] }};"></span>
                                <span class="flex-1 truncate">{{ $c['name'] }}</span>
                                @if($isSelected) <span>✓</span> @endif
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- Price Range -->
                <div>
                    <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wide mb-2">Price Range (BDT)</h4>
                    <div class="grid grid-cols-2 gap-2">
                        <input
                            type="number"
                            wire:model.live.debounce.400ms="minPrice"
                            placeholder="Min ৳"
                            class="w-full text-xs p-2 rounded-lg bg-stone-50 border border-stone-200"
                        >
                        <input
                            type="number"
                            wire:model.live.debounce.400ms="maxPrice"
                            placeholder="Max ৳"
                            class="w-full text-xs p-2 rounded-lg bg-stone-50 border border-stone-200"
                        >
                    </div>
                </div>

                <!-- In Stock -->
                <div class="flex items-center justify-between pt-2 border-t border-stone-100">
                    <span class="text-xs font-bold text-slate-900">In-Stock Only</span>
                    <input type="checkbox" wire:model.live="inStockOnly" class="h-4 w-4 text-rose-900 rounded border-stone-300">
                </div>
            </div>

            <!-- Drawer Bottom Buttons -->
            <div class="p-4 border-t border-stone-200 bg-stone-50 flex items-center gap-2">
                <button
                    type="button"
                    wire:click="clearFilters"
                    class="flex-1 py-2.5 rounded-xl border border-stone-300 text-slate-700 text-xs font-bold hover:bg-stone-100 transition"
                >
                    Reset
                </button>
                <button
                    type="button"
                    @click="mobileFilterOpen = false"
                    class="flex-1 py-2.5 rounded-xl bg-rose-900 text-white text-xs font-bold hover:bg-rose-800 transition shadow-sm"
                >
                    Show ({{ $products->total() }})
                </button>
            </div>
        </div>
    </div>

</div>