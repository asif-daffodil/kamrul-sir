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
        $this->app->singleton(\App\Services\Sms\SmsManager::class, function () {
            return new \App\Services\Sms\SmsManager();
        });

        $this->app->bind(\App\Services\Sms\SmsProviderInterface::class, function ($app) {
            return $app->make(\App\Services\Sms\SmsManager::class)->getProvider();
        });
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

        // Merge guest cart into user cart upon login
        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Auth\Events\Login::class,
            \App\Listeners\MergeGuestCartOnLogin::class
        );

        // Explicit Policy Registrations
        \Illuminate\Support\Facades\Gate::policy(\App\Models\Order::class, \App\Policies\OrderPolicy::class);
        \Illuminate\Support\Facades\Gate::policy(\App\Models\ExchangeRequest::class, \App\Policies\ExchangeRequestPolicy::class);
    }
}
