<?php

namespace App\Core\Infrastructure\Repositories;

use App\Core\Application\Contracts\PackageRepositoryInterface;
use App\Modules\Package\Models\Package;
use Illuminate\Database\Eloquent\Collection;

class EloquentPackageRepository implements PackageRepositoryInterface
{
    /**
     * List all active packages.
     */
    public function listActive(): Collection
    {
        return Package::active()->with('services')->orderBy('name')->get();
    }

    /**
     * Find package by ID.
     */
    public function find(int $id): ?Package
    {
        return Package::find($id);
    }

    /**
     * Create a new package.
     */
    public function create(array $data): Package
    {
        return Package::create($data);
    }

    /**
     * Update package.
     */
    public function update(Package $package, array $data): bool
    {
        return $package->update($data);
    }

    /**
     * Delete package.
     */
    public function delete(Package $package): bool
    {
        return $package->delete();
    }

    /**
     * Get all packages.
     */
    public function all(): Collection
    {
        return Package::orderBy('name')->get();
    }

    /**
     * Get packages by IDs.
     */
    public function findByIds(array $ids): Collection
    {
        return Package::whereIn('id', $ids)->get();
    }

    /**
     * Validate package price.
     */
    public function validatePrice(Package $package): bool
    {
        $package->load('services');
        $servicesTotal = $package->services->sum('price');
        
        return $package->price < $servicesTotal;
    }
}
