<?php

namespace App\Modules\Service\Controllers;

use App\Core\Application\Contracts\ServiceRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Modules\Service\Models\Service;
use App\Modules\Service\Resources\ServiceResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

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
    public function index(Request $request): JsonResponse
    {
        $perPage = min($request->get('per_page', 15), 100);
        
        $services = Service::where('is_active', true)
            ->orderBy('name')
            ->paginate($perPage);

        return ApiResponse::paginated(new ServiceResource($services));
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
    public function show(int $id): JsonResponse
    {
        $service = Service::where('is_active', true)
            ->findOrFail($id);

        return ApiResponse::success(new ServiceResource($service));
    }
}
