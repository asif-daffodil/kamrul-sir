<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    #[Url(as: 'category')]
    public ?string $categorySlug = null;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'sizes')]
    public array $selectedSizes = [];

    #[Url(as: 'colors')]
    public array $selectedColors = [];

    #[Url(as: 'min_price')]
    public ?float $minPrice = null;

    #[Url(as: 'max_price')]
    public ?float $maxPrice = null;

    #[Url(as: 'sort')]
    public string $sort = 'latest';

    #[Url(as: 'in_stock')]
    public bool $inStockOnly = false;

    public bool $mobileFilterOpen = false;

    // Available size options across the store
    public array $allSizes = ['35', '36', '37', '38', '39', '40', '41', '42'];

    // Standardized color swatches
    public array $allColors = [
        ['name' => 'Midnight Black', 'code' => '#111827'],
        ['name' => 'Deep Maroon', 'code' => '#800000'],
        ['name' => 'Soft Nude', 'code' => '#E8C5B0'],
        ['name' => 'Rose Gold', 'code' => '#B76E79'],
        ['name' => 'Champagne Gold', 'code' => '#D4AF37'],
        ['name' => 'Burnished Tan', 'code' => '#A0522D'],
        ['name' => 'Silver Chrome', 'code' => '#C0C0C0'],
        ['name' => 'Cream White', 'code' => '#FFFDD0'],
    ];

    public function mount(?string $categorySlug = null): void
    {
        if ($categorySlug) {
            $this->categorySlug = $categorySlug;
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedSelectedSizes(): void
    {
        $this->resetPage();
    }

    public function updatedSelectedColors(): void
    {
        $this->resetPage();
    }

    public function updatedMinPrice(): void
    {
        $this->resetPage();
    }

    public function updatedMaxPrice(): void
    {
        $this->resetPage();
    }

    public function updatedSort(): void
    {
        $this->resetPage();
    }

    public function updatedInStockOnly(): void
    {
        $this->resetPage();
    }

    public function toggleSize(string $size): void
    {
        if (in_array($size, $this->selectedSizes)) {
            $this->selectedSizes = array_values(array_diff($this->selectedSizes, [$size]));
        } else {
            $this->selectedSizes[] = $size;
        }
        $this->resetPage();
    }

    public function toggleColor(string $color): void
    {
        if (in_array($color, $this->selectedColors)) {
            $this->selectedColors = array_values(array_diff($this->selectedColors, [$color]));
        } else {
            $this->selectedColors[] = $color;
        }
        $this->resetPage();
    }

    public function setPriceRange(?float $min, ?float $max): void
    {
        $this->minPrice = $min;
        $this->maxPrice = $max;
        $this->resetPage();
    }

    public function selectCategory(?string $slug): void
    {
        $this->categorySlug = $slug;
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->selectedSizes = [];
        $this->selectedColors = [];
        $this->minPrice = null;
        $this->maxPrice = null;
        $this->inStockOnly = false;
        $this->sort = 'latest';
        $this->resetPage();
    }

    public function getActiveFiltersCountProperty(): int
    {
        $count = 0;
        if (! empty($this->search)) $count++;
        if (! empty($this->selectedSizes)) $count += count($this->selectedSizes);
        if (! empty($this->selectedColors)) $count += count($this->selectedColors);
        if ($this->minPrice !== null || $this->maxPrice !== null) $count++;
        if ($this->inStockOnly) $count++;
        return $count;
    }

    #[Computed]
    public function currentCategory(): ?Category
    {
        if (! $this->categorySlug) {
            return null;
        }

        return Category::where('slug', $this->categorySlug)
            ->with(['children', 'parent'])
            ->first();
    }

    #[Computed]
    public function suggestions(): array
    {
        $q = trim($this->search);
        if (strlen($q) < 2) {
            return [];
        }

        return Product::active()
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                    ->orWhereHas('category', fn ($c) => $c->where('name', 'like', "%{$q}%"));
            })
            ->with(['category', 'primaryImage', 'variants'])
            ->take(5)
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'category' => $p->category?->name,
                'price' => $p->effective_price,
                'image_url' => $p->image_url,
            ])
            ->toArray();
    }

    public function selectSuggestion(string $productName): void
    {
        $this->search = $productName;
        $this->resetPage();
    }

    #[Computed]
    public function categories(): \Illuminate\Database\Eloquent\Collection
    {
        return Category::roots()
            ->active()
            ->with(['children' => fn ($q) => $q->active()])
            ->withCount(['products' => fn ($q) => $q->active()])
            ->orderBy('sort_order', 'asc')
            ->get();
    }

    public function with(): array
    {
        $query = Product::active()
            ->with(['primaryImage', 'variants', 'approvedReviews', 'category']);

        // 1. Category Filtering (including subcategories)
        if ($this->currentCategory) {
            $catIds = [$this->currentCategory->id];
            if ($this->currentCategory->children->isNotEmpty()) {
                $catIds = array_merge($catIds, $this->currentCategory->children->pluck('id')->toArray());
            }
            $query->whereIn('category_id', $catIds);
        }

        // 2. Keyword Search
        if (! empty(trim($this->search))) {
            $term = trim($this->search);
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('short_description', 'like', "%{$term}%")
                    ->orWhereHas('category', fn ($c) => $c->where('name', 'like', "%{$term}%"));
            });
        }

        // 3. Size Filter
        if (! empty($this->selectedSizes)) {
            $sizes = $this->selectedSizes;
            $query->whereHas('variants', function ($v) use ($sizes) {
                $v->whereIn('size', $sizes)->where('stock', '>', 0);
            });
        }

        // 4. Color Filter
        if (! empty($this->selectedColors)) {
            $colors = $this->selectedColors;
            $query->whereHas('variants', function ($v) use ($colors) {
                $v->whereIn('color', $colors);
            });
        }

        // 5. Price Range Filter
        if ($this->minPrice !== null) {
            $min = $this->minPrice;
            $query->whereHas('variants', function ($v) use ($min) {
                $v->where(function ($sub) use ($min) {
                    $sub->where('sale_price', '>=', $min)
                        ->orWhere(fn ($p) => $p->whereNull('sale_price')->where('price', '>=', $min));
                });
            });
        }

        if ($this->maxPrice !== null) {
            $max = $this->maxPrice;
            $query->whereHas('variants', function ($v) use ($max) {
                $v->where(function ($sub) use ($max) {
                    $sub->where('sale_price', '<=', $max)
                        ->orWhere(fn ($p) => $p->whereNull('sale_price')->where('price', '<=', $max));
                });
            });
        }

        // 6. In-Stock Filter
        if ($this->inStockOnly) {
            $query->whereHas('variants', function ($v) {
                $v->where('stock', '>', 0);
            });
        }

        // 7. Sorting
        match ($this->sort) {
            'price_asc' => $query->orderBy('base_price', 'asc'),
            'price_desc' => $query->orderBy('base_price', 'desc'),
            'popular' => $query->orderBy('is_featured', 'desc')->orderBy('id', 'desc'),
            'rating' => $query->withAvg('approvedReviews', 'rating')->orderByDesc('approved_reviews_avg_rating')->orderBy('id', 'desc'),
            default => $query->orderBy('id', 'desc'), // latest
        };

        $products = $query->paginate(12);

        return [
            'products' => $products,
        ];
    }
};
