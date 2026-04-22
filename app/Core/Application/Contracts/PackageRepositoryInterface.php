<?php

namespace App\Core\Application\Contracts;

use App\Modules\Package\Models\Package;
use Illuminate\Database\Eloquent\Collection;

interface PackageRepositoryInterface
{
    /**
     * List all active packages.
     */
    public function listActive(): Collection;

    /**
     * Find package by ID.
     */
    public function find(int $id): ?Package;

    /**
     * Create a new package.
     */
    public function create(array $data): Package;

    /**
     * Update package.
     */
    public function update(Package $package, array $data): bool;

    /**
     * Delete package.
     */
    public function delete(Package $package): bool;

    /**
     * Get all packages.
     */
    public function all(): Collection;

    /**
     * Get packages by IDs.
     */
    public function findByIds(array $ids): Collection;

    /**
     * Validate package price.
     */
    public function validatePrice(Package $package): bool;
}
