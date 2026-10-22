<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(! app()->isProduction());

        // Ensure navigation categories are always available to storefront layout
        \Illuminate\Support\Facades\View::composer('layouts.storefront', function ($view) {
            if (! isset($view->categories)) {
                $categories = \App\Models\Category::roots()
                    ->active()
                    ->with(['children' => fn ($q) => $q->active()])
                    ->withCount(['products' => fn ($q) => $q->active()])
                    ->orderBy('sort_order')
                    ->get();
                $view->with('categories', $categories);
            }
        });
    }
}
