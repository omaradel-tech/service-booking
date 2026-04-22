<?php

namespace App\Core\Application\Contracts;

use App\Modules\Service\Models\Service;
use Illuminate\Database\Eloquent\Collection;

interface ServiceRepositoryInterface
{
    /**
     * List all active services.
     */
    public function listActive(): Collection;

    /**
     * Find service by ID.
     */
    public function find(int $id): ?Service;

    /**
     * Create a new service.
     */
    public function create(array $data): Service;

    /**
     * Update service.
     */
    public function update(Service $service, array $data): bool;

    /**
     * Delete service.
     */
    public function delete(Service $service): bool;

    /**
     * Get all services.
     */
    public function all(): Collection;

    /**
     * Get services by IDs.
     */
    public function findByIds(array $ids): Collection;
}
