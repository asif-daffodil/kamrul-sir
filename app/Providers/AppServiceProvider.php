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

        // Enforce HTTPS URLs in production or behind SSL proxies/CDNs
        if ($this->app->isProduction() || request()->isSecure() || request()->header('X-Forwarded-Proto') === 'https') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Filament Notifications Position (Top-Center Flash / SweetAlert style)
        \Filament\Notifications\Livewire\Notifications::alignment(\Filament\Support\Enums\Alignment::Center);
        \Filament\Notifications\Livewire\Notifications::verticalAlignment(\Filament\Support\Enums\VerticalAlignment::Start);

        // Global Validation Error Notification on all Filament Form Pages
        \Filament\Pages\BasePage::$reportValidationErrorUsing = function (\Illuminate\Validation\ValidationException $exception) {
            $errors = collect($exception->validator->errors()->all())->take(4)->implode(' • ');

            \Filament\Notifications\Notification::make()
                ->title('Validation Failed')
                ->body($errors ?: 'Please check the form for highlighted errors and required fields.')
                ->danger()
                ->persistent()
                ->send();
        };

        // Ensure navigation categories are always available to storefront layout (cached)
        \Illuminate\Support\Facades\View::composer('layouts.storefront', function ($view) {
            if (! isset($view->categories)) {
                $view->with('categories', \App\Models\Category::getCachedNavHierarchy());
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

        // Security Rate Limiters
        \Illuminate\Support\Facades\RateLimiter::for('login', function (\Illuminate\Http\Request $request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(5)->by($request->ip());
        });

        \Illuminate\Support\Facades\RateLimiter::for('checkout', function (\Illuminate\Http\Request $request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(10)->by($request->ip());
        });

        \Illuminate\Support\Facades\RateLimiter::for('tracking', function (\Illuminate\Http\Request $request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(20)->by($request->ip());
        });

        \Illuminate\Support\Facades\RateLimiter::for('api-search', function (\Illuminate\Http\Request $request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(60)->by($request->ip());
        });
    }
}
