<?php

namespace App\Core\Infrastructure\Repositories;

use App\Core\Application\Contracts\ServiceRepositoryInterface;
use App\Modules\Service\Models\Service;
use Illuminate\Database\Eloquent\Collection;

class EloquentServiceRepository implements ServiceRepositoryInterface
{
    /**
     * List all active services.
     */
    public function listActive(): Collection
    {
        return Service::active()->orderBy('name')->get();
    }

    /**
     * Find service by ID.
     */
    public function find(int $id): ?Service
    {
        return Service::find($id);
    }

    /**
     * Create a new service.
     */
    public function create(array $data): Service
    {
        return Service::create($data);
    }

    /**
     * Update service.
     */
    public function update(Service $service, array $data): bool
    {
        return $service->update($data);
    }

    /**
     * Delete service.
     */
    public function delete(Service $service): bool
    {
        return $service->delete();
    }

    /**
     * Get all services.
     */
    public function all(): Collection
    {
        return Service::orderBy('name')->get();
    }

    /**
     * Get services by IDs.
     */
    public function findByIds(array $ids): Collection
    {
        return Service::whereIn('id', $ids)->get();
    }
}
