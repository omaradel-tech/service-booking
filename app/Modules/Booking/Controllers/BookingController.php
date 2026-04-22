<?php

namespace App\Modules\Booking\Controllers;

use App\Core\Application\DTOs\CreateBookingDTO;
use App\Core\Application\Services\BookingService;
use App\Http\Controllers\Controller;
use App\Modules\Booking\Requests\CreateBookingRequest;
use App\Modules\Booking\Resources\BookingResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * @group Bookings
 * 
 * Booking management endpoints for scheduling and managing service appointments.
 * 
 * @authenticated
 */
class BookingController extends Controller
{
    public function __construct(
        private BookingService $bookingService
    ) {}

    /**
     * List user bookings
     * 
     * Returns all bookings for the authenticated user.
     * 
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "user_id": 1,
     *       "service_id": 1,
     *       "scheduled_at": "2023-01-15T10:00:00.000000Z",
     *       "status": "pending",
     *       "service": {
     *         "id": 1,
     *         "name": "AC Repair",
     *         "price": "150.00"
     *       }
     *     }
     *   ]
     * }
     */
    public function index(Request $request): Response
    {
        $bookings = $this->bookingService->getForUser($request->user());

        return response([
            'data' => BookingResource::collection($bookings),
        ]);
    }

    /**
     * Create a new booking
     * 
     * Creates a new booking for a service at the specified time.
     * 
     * @bodyParam service_id integer required ID of the service to book. Example: 1
     * @bodyParam scheduled_at string required Booking time in ISO 8601 format. Example: 2023-01-15T10:00:00Z
     * 
     * @response 201 {
     *   "data": {
     *     "id": 1,
     *     "user_id": 1,
     *     "service_id": 1,
     *     "scheduled_at": "2023-01-15T10:00:00.000000Z",
     *     "status": "pending",
     *     "service": {
     *       "id": 1,
     *       "name": "AC Repair",
     *       "price": "150.00"
     *     }
     *   },
     *   "message": "Booking created successfully"
     * }
     * @response 400 {
     *   "message": "Active subscription required to create booking"
     * }
     */
    public function store(CreateBookingRequest $request): Response
    {
        try {
            $dto = new CreateBookingDTO(
                user: $request->user(),
                serviceId: $request->service_id,
                scheduledAt: \Carbon\Carbon::parse($request->scheduled_at)
            );

            $booking = $this->bookingService->create($dto);
            $booking->load('service');

            return response([
                'data' => new BookingResource($booking),
                'message' => 'Booking created successfully',
            ], 201);
        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get booking details
     * 
     * Returns detailed information about a specific booking.
     * 
     * @urlParam id integer required Booking ID. Example: 1
     * 
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "user_id": 1,
     *     "service_id": 1,
     *     "scheduled_at": "2023-01-15T10:00:00.000000Z",
     *     "status": "pending",
     *     "service": {
     *       "id": 1,
     *       "name": "AC Repair",
     *       "price": "150.00"
     *     }
     *   }
     * }
     * @response 404 {
     *   "message": "Booking not found"
     * }
     */
    public function show(int $id, Request $request): Response
    {
        $bookings = $this->bookingService->getForUser($request->user());
        $booking = $bookings->firstWhere('id', $id);

        if (!$booking) {
            return response([
                'message' => 'Booking not found',
            ], 404);
        }

        return response([
            'data' => new BookingResource($booking),
        ]);
    }

    /**
     * Cancel a booking
     * 
     * Cancels a booking if it can be canceled (pending or confirmed status and future time).
     * 
     * @urlParam id integer required Booking ID. Example: 1
     * 
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "status": "canceled"
     *   },
     *   "message": "Booking canceled successfully"
     * }
     * @response 400 {
     *   "message": "Booking cannot be canceled"
     * }
     * @response 404 {
     *   "message": "Booking not found"
     * }
     */
    public function cancel(int $id, Request $request): Response
    {
        $bookings = $this->bookingService->getForUser($request->user());
        $booking = $bookings->firstWhere('id', $id);

        if (!$booking) {
            return response([
                'message' => 'Booking not found',
            ], 404);
        }

        try {
            $this->bookingService->cancel($booking);

            return response([
                'data' => new BookingResource($booking),
                'message' => 'Booking canceled successfully',
            ]);
        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
