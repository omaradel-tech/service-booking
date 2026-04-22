<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use App\Modules\Booking\Models\Booking;
use App\Modules\Booking\Policies\BookingPolicy;
use App\Modules\Cart\Models\CartItem;
use App\Modules\Cart\Policies\CartItemPolicy;
use App\Modules\Subscription\Models\Subscription;
use App\Modules\Subscription\Policies\SubscriptionPolicy;

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
        // Register policies
        Gate::policy(Booking::class, BookingPolicy::class);
        Gate::policy(CartItem::class, CartItemPolicy::class);
        Gate::policy(Subscription::class, SubscriptionPolicy::class);

        // Define rate limiters
        $this->configureRateLimiting();
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        // Auth endpoints: 5 requests per minute per IP + email combination
        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip() . ':' . $request->input('email'));
        });

        // General API: 60 requests per minute per user or IP
        RateLimiter::for('api', function (Request $request) {
            return $request->user()
                ? Limit::perMinute(60)->by($request->user()->id)
                : Limit::perMinute(60)->by($request->ip());
        });

        // Booking operations: 10 requests per minute per user
        RateLimiter::for('booking', function (Request $request) {
            return $request->user()
                ? Limit::perMinute(10)->by($request->user()->id)
                : Limit::perMinute(10)->by($request->ip());
        });

        // Checkout operations: 5 requests per minute per user
        RateLimiter::for('checkout', function (Request $request) {
            return $request->user()
                ? Limit::perMinute(5)->by($request->user()->id)
                : Limit::perMinute(5)->by($request->ip());
        });
    }
}
