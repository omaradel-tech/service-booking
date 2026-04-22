<?php

namespace App\Providers;

use App\Core\Application\Contracts\SubscriptionRepositoryInterface;
use App\Core\Application\Contracts\BookingRepositoryInterface;
use App\Core\Application\Contracts\CartRepositoryInterface;
use App\Core\Application\Contracts\ServiceRepositoryInterface;
use App\Core\Application\Contracts\PackageRepositoryInterface;
use App\Core\Infrastructure\Repositories\EloquentSubscriptionRepository;
use App\Core\Infrastructure\Repositories\EloquentBookingRepository;
use App\Core\Infrastructure\Repositories\EloquentCartRepository;
use App\Core\Infrastructure\Repositories\EloquentServiceRepository;
use App\Core\Infrastructure\Repositories\EloquentPackageRepository;
use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Repository bindings
        $this->app->bind(SubscriptionRepositoryInterface::class, EloquentSubscriptionRepository::class);
        $this->app->bind(BookingRepositoryInterface::class, EloquentBookingRepository::class);
        $this->app->bind(CartRepositoryInterface::class, EloquentCartRepository::class);
        $this->app->bind(ServiceRepositoryInterface::class, EloquentServiceRepository::class);
        $this->app->bind(PackageRepositoryInterface::class, EloquentPackageRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load module migrations
        $this->loadMigrationsFrom([
            app_path('Modules/Subscription/Migrations'),
            app_path('Modules/Service/Migrations'),
            app_path('Modules/Booking/Migrations'),
            app_path('Modules/Cart/Migrations'),
            app_path('Modules/Package/Migrations'),
        ]);

        // Load module factories
        $this->loadFactoriesFrom([
            app_path('Modules/Subscription/Factories'),
            app_path('Modules/Service/Factories'),
            app_path('Modules/Booking/Factories'),
            app_path('Modules/Package/Factories'),
        ]);
    }
}
