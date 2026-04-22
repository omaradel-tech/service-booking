<?php

namespace App\Core\Application\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class CacheService
{
    /**
     * Cache duration in seconds.
     */
    private const DEFAULT_TTL = 3600; // 1 hour
    private const LONG_TTL = 86400; // 24 hours

    /**
     * Get cached data or execute callback and cache result.
     */
    public function remember(string $key, callable $callback, int $ttl = self::DEFAULT_TTL): mixed
    {
        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * Get cached data for services.
     */
    public function rememberActiveServices(callable $callback): mixed
    {
        return $this->remember('services.active', $callback, self::DEFAULT_TTL);
    }

    /**
     * Get cached data for packages.
     */
    public function rememberActivePackages(callable $callback): mixed
    {
        return $this->remember('packages.active', $callback, self::DEFAULT_TTL);
    }

    /**
     * Get cached user subscription.
     */
    public function rememberUserSubscription(int $userId, callable $callback): mixed
    {
        return $this->remember("user.{$userId}.subscription", $callback, self::DEFAULT_TTL);
    }

    /**
     * Get cached user cart.
     */
    public function rememberUserCart(int $userId, callable $callback): mixed
    {
        return $this->remember("user.{$userId}.cart", $callback, self::DEFAULT_TTL);
    }

    /**
     * Get cached user bookings.
     */
    public function rememberUserBookings(int $userId, callable $callback): mixed
    {
        return $this->remember("user.{$userId}.bookings", $callback, self::DEFAULT_TTL);
    }

    /**
     * Forget cached data.
     */
    public function forget(string $key): bool
    {
        return Cache::forget($key);
    }

    /**
     * Forget user-specific cache.
     */
    public function forgetUserCache(int $userId): void
    {
        $patterns = [
            "user.{$userId}.subscription",
            "user.{$userId}.cart",
            "user.{$userId}.bookings",
        ];

        foreach ($patterns as $key) {
            $this->forget($key);
        }
    }

    /**
     * Forget service-related cache.
     */
    public function forgetServiceCache(): void
    {
        $this->forget('services.active');
        $this->forget('packages.active');
    }

    /**
     * Clear all cache (use with caution).
     */
    public function clear(): bool
    {
        return Cache::flush();
    }

    /**
     * Check if cache driver is Redis.
     */
    public function isRedisDriver(): bool
    {
        return config('cache.default') === 'redis';
    }

    /**
     * Get cache statistics (Redis only).
     */
    public function getStats(): array
    {
        if (!$this->isRedisDriver()) {
            return ['message' => 'Cache statistics only available with Redis driver'];
        }

        try {
            $redis = Redis::connection('cache');
            
            return [
                'info' => $redis->info('memory'),
                'keys_count' => $redis->dbSize(),
                'driver' => 'redis',
            ];
        } catch (\Exception $e) {
            return ['error' => 'Unable to retrieve Redis stats: ' . $e->getMessage()];
        }
    }
}
