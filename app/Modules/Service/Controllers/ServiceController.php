<?php

namespace App\Modules\Service\Controllers;

use App\Core\Application\Contracts\ServiceRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Modules\Service\Resources\ServiceResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * @group Services
 * 
 * Service management endpoints for browsing available maintenance services.
 */
class ServiceController extends Controller
{
    public function __construct(
        private ServiceRepositoryInterface $serviceRepository
    ) {}

    /**
     * List all active services
     * 
     * Returns a list of all currently active maintenance services.
     * 
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "AC Repair",
     *       "description": "Complete air conditioning repair service",
     *       "price": "150.00",
     *       "duration_minutes": 120,
     *       "is_active": true
     *     }
     *   ]
     * }
     */
    public function index(): Response
    {
        $services = $this->serviceRepository->listActive();

        return response([
            'data' => ServiceResource::collection($services),
        ]);
    }

    /**
     * Get service details
     * 
     * Returns detailed information about a specific service.
     * 
     * @urlParam id integer required Service ID. Example: 1
     * 
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "name": "AC Repair",
     *     "description": "Complete air conditioning repair service",
     *     "price": "150.00",
     *     "duration_minutes": 120,
     *     "is_active": true
     *   }
     * }
     * @response 404 {
     *   "message": "Service not found"
     * }
     */
    public function show(int $id): Response
    {
        $service = $this->serviceRepository->find($id);

        if (!$service) {
            return response([
                'message' => 'Service not found',
            ], 404);
        }

        return response([
            'data' => new ServiceResource($service),
        ]);
    }
}
