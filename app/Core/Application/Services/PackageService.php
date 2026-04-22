<?php

namespace App\Core\Application\Services;

use App\Core\Application\Contracts\PackageRepositoryInterface;
use App\Core\Application\Contracts\ServiceRepositoryInterface;
use App\Modules\Package\Models\Package;
use App\Modules\Service\Models\Service;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PackageService
{
    public function __construct(
        private PackageRepositoryInterface $packageRepository,
        private ServiceRepositoryInterface $serviceRepository
    ) {}

    /**
     * List all active packages with caching.
     */
    public function listActive(): \Illuminate\Database\Eloquent\Collection
    {
        // Temporarily disable caching to fix __PHP_Incomplete_Class issue
        // return Cache::remember('packages.active', 3600, function () {
        //     return $this->packageRepository->listActive();
        // });
        
        return $this->packageRepository->listActive();
    }

    /**
     * Find package by ID with caching.
     */
    public function find(int $id): ?Package
    {
        return Cache::remember("packages.{$id}", 3600, function () use ($id) {
            return $this->packageRepository->find($id);
        });
    }

    /**
     * Create a new package with validation.
     */
    public function create(array $data): Package
    {
        $package = $this->packageRepository->create($data);

        // Clear cache
        $this->clearPackageCache();

        Log::info('Package created', [
            'package_id' => $package->id,
            'name' => $package->name,
        ]);

        return $package;
    }

    /**
     * Update package with validation.
     */
    public function update(Package $package, array $data): bool
    {
        $updated = $this->packageRepository->update($package, $data);

        if ($updated) {
            // Clear cache
            $this->clearPackageCache();

            Log::info('Package updated', [
                'package_id' => $package->id,
            ]);
        }

        return $updated;
    }

    /**
     * Delete package.
     */
    public function delete(Package $package): bool
    {
        $deleted = $this->packageRepository->delete($package);

        if ($deleted) {
            // Clear cache
            $this->clearPackageCache();

            Log::info('Package deleted', [
                'package_id' => $package->id,
            ]);
        }

        return $deleted;
    }

    /**
     * Validate package price against services total.
     */
    public function validatePrice(Package $package): bool
    {
        return $this->packageRepository->validatePrice($package);
    }

    /**
     * Add services to package.
     */
    public function addServices(Package $package, array $serviceIds): bool
    {
        $services = $this->serviceRepository->findByIds($serviceIds);
        
        if ($services->count() !== count($serviceIds)) {
            throw new \Exception('Some services not found');
        }

        $package->services()->syncWithoutDetaching($serviceIds);

        // Validate price after adding services
        if (!$this->validatePrice($package)) {
            throw new \Exception('Package price must be less than sum of service prices');
        }

        // Clear cache
        $this->clearPackageCache();

        Log::info('Services added to package', [
            'package_id' => $package->id,
            'service_ids' => $serviceIds,
        ]);

        return true;
    }

    /**
     * Remove services from package.
     */
    public function removeServices(Package $package, array $serviceIds): bool
    {
        $package->services()->detach($serviceIds);

        // Clear cache
        $this->clearPackageCache();

        Log::info('Services removed from package', [
            'package_id' => $package->id,
            'service_ids' => $serviceIds,
        ]);

        return true;
    }

    /**
     * Get package with services.
     */
    public function getWithServices(int $id): ?Package
    {
        $package = $this->find($id);
        
        if ($package) {
            $package->load('services');
        }

        return $package;
    }

    /**
     * Clear package cache.
     */
    private function clearPackageCache(): void
    {
        Cache::tags(['packages'])->flush();
    }
}
