<?php

namespace App\Modules\Cart\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use App\Modules\Cart\Requests\CheckoutRequest;
use App\Modules\Cart\Services\CheckoutService;
use App\Modules\Cart\DTOs\CheckoutDTO;
use App\Modules\Booking\Resources\BookingResource;
use Illuminate\Http\JsonResponse;

class CheckoutController extends Controller
{
    public function __construct(
        private CheckoutService $checkoutService
    ) {}

    /**
     * Process checkout - convert cart items to bookings.
     */
    public function checkout(CheckoutRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $checkoutDTO = CheckoutDTO::fromArray($request->validated());
        $bookings = $this->checkoutService->checkout($user, $checkoutDTO);

        return ApiResponse::success(
            data: BookingResource::collection($bookings),
            status: 201
        );
    }
}
