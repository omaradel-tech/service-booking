<?php

namespace App\Modules\Package\Controllers;

use App\Core\Application\Services\PackageService;
use App\Http\Controllers\Controller;
use App\Modules\Package\Resources\PackageResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * @group Packages
 * 
 * Package management endpoints for browsing service bundles.
 */
class PackageController extends Controller
{
    public function __construct(
        private PackageService $packageService
    ) {}

    /**
     * List all active packages
     * 
     * Returns a list of all currently active service packages with their included services.
     * 
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "Complete Home Maintenance",
     *       "price": "500.00",
     *       "is_active": true,
     *       "services_total_price": "600.00",
     *       "discount_amount": "100.00",
     *       "discount_percentage": "16.67",
     *       "services": [
     *         {
     *           "id": 1,
     *           "name": "AC Repair",
     *           "price": "150.00"
     *         }
     *       ]
     *     }
     *   ]
     * }
     */
    public function index(): Response
    {
        $packages = $this->packageService->listActive();

        return response([
            'data' => PackageResource::collection($packages),
        ]);
    }

    /**
     * Get package details
     * 
     * Returns detailed information about a specific package including all included services.
     * 
     * @urlParam id integer required Package ID. Example: 1
     * 
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "name": "Complete Home Maintenance",
     *     "price": "500.00",
     *     "is_active": true,
     *     "services_total_price": "600.00",
     *     "discount_amount": "100.00",
     *     "discount_percentage": "16.67",
     *     "services": [
     *       {
     *         "id": 1,
     *         "name": "AC Repair",
     *         "price": "150.00",
     *         "description": "Complete air conditioning repair service",
     *         "duration_minutes": 120
     *       }
     *     ]
     *   }
     * }
     * @response 404 {
     *   "message": "Package not found"
     * }
     */
    public function show(int $id): Response
    {
        $package = $this->packageService->getWithServices($id);

        if (!$package) {
            return response([
                'message' => 'Package not found',
            ], 404);
        }

        return response([
            'data' => new PackageResource($package),
        ]);
    }
}
