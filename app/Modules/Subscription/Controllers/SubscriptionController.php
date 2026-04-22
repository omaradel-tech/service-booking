<?php

namespace App\Modules\Subscription\Controllers;

use App\Core\Application\Services\SubscriptionService;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Modules\Subscription\Resources\SubscriptionResource;
use App\Exceptions\Domain\SubscriptionInactiveException;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @group Subscriptions
 * 
 * Subscription management endpoints for viewing and managing user subscriptions.
 * 
 * @authenticated
 */
class SubscriptionController extends Controller
{
    public function __construct(
        private SubscriptionService $subscriptionService
    ) {}

    /**
     * Get current subscription
     * 
     * Returns the authenticated user's current active subscription.
     * 
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "user_id": 1,
     *     "type": "trial",
     *     "status": "active",
     *     "starts_at": "2023-01-01T00:00:00.000000Z",
     *     "ends_at": "2023-01-31T23:59:59.000000Z",
     *     "grace_ends_at": "2023-02-07T23:59:59.000000Z",
     *     "is_active": true,
     *     "is_in_grace_period": false
     *   }
     * }
     * @response 404 {
     *   "error": {
     *     "code": "NOT_FOUND",
     *     "message": "Resource not found"
     *   }
     * }
     */
    public function current(Request $request): JsonResponse
    {
        $subscription = $this->subscriptionService->getCurrent($request->user());

        if (!$subscription) {
            return ApiResponse::error('NOT_FOUND', 'No active subscription found', status: 404);
        }

        return ApiResponse::success(new SubscriptionResource($subscription));
    }

    /**
     * Cancel subscription
     * 
     * Cancels the user's current active subscription.
     * 
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "status": "canceled"
     *   },
     *   "message": "Subscription canceled successfully"
     * }
     * @response 400 {
     *   "message": "No active subscription found"
     * }
     */
    public function cancel(Request $request): JsonResponse
    {
        try {
            $this->subscriptionService->cancel($request->user());

            $subscription = $this->subscriptionService->getCurrent($request->user());

            return ApiResponse::success(
                data: $subscription ? new SubscriptionResource($subscription) : null,
                meta: ['message' => 'Subscription canceled successfully']
            );
        } catch (\Exception $e) {
            return ApiResponse::error('SUBSCRIPTION_ERROR', $e->getMessage(), status: 400);
        }
    }

    /**
     * Check subscription status
     * 
     * Returns whether the user has an active subscription (including grace period).
     * 
     * @response 200 {
     *   "data": {
     *     "has_active_subscription": true,
     *     "subscription": {
     *       "id": 1,
     *       "type": "trial",
     *       "status": "active",
     *       "ends_at": "2023-01-31T23:59:59.000000Z"
     *     }
     *   }
     * }
     */
    public function check(Request $request): JsonResponse
    {
        $hasActive = $this->subscriptionService->checkActive($request->user());
        $subscription = $this->subscriptionService->getCurrent($request->user());

        return ApiResponse::success([
            'active' => $hasActive,
            'status' => $subscription?->status,
            'days_remaining' => $subscription && $subscription->ends_at ? $subscription->ends_at->diffInDays(now()) : null,
            'subscription' => $subscription ? new SubscriptionResource($subscription) : null,
        ]);
    }

    /**
     * Start trial subscription
     * 
     * Starts a new trial subscription for the user if no active subscription exists.
     * 
     * @response 201 {
     *   "data": {
     *     "id": 1,
     *     "type": "trial",
     *     "status": "active",
     *     "starts_at": "2023-01-01T00:00:00.000000Z",
     *     "ends_at": "2023-01-31T23:59:59.000000Z",
     *     "grace_ends_at": "2023-02-07T23:59:59.000000Z"
     *   }
     * }
     * @response 403 {
     *   "error": {
     *     "code": "SUBSCRIPTION_INACTIVE",
     *     "message": "User already has an active subscription"
     *   }
     * }
     */
    public function startTrial(Request $request): JsonResponse
    {
        $currentSubscription = $this->subscriptionService->getCurrent($request->user());
        
        if ($currentSubscription && $currentSubscription->isActive()) {
            throw new SubscriptionInactiveException('User already has an active subscription');
        }

        $subscription = $this->subscriptionService->createTrial($request->user());

        return ApiResponse::success(
            data: new SubscriptionResource($subscription),
            status: 201
        );
    }
}
