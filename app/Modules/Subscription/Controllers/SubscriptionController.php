<?php

namespace App\Modules\Subscription\Controllers;

use App\Core\Application\Services\SubscriptionService;
use App\Http\Controllers\Controller;
use App\Modules\Subscription\Resources\SubscriptionResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

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
     *   "message": "No active subscription found"
     * }
     */
    public function current(Request $request): Response
    {
        $subscription = $this->subscriptionService->getCurrent($request->user());

        if (!$subscription) {
            return response([
                'message' => 'No active subscription found',
            ], 404);
        }

        return response([
            'data' => new SubscriptionResource($subscription),
        ]);
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
    public function cancel(Request $request): Response
    {
        try {
            $this->subscriptionService->cancel($request->user());

            $subscription = $this->subscriptionService->getCurrent($request->user());

            return response([
                'data' => $subscription ? new SubscriptionResource($subscription) : null,
                'message' => 'Subscription canceled successfully',
            ]);
        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage(),
            ], 400);
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
    public function check(Request $request): Response
    {
        $hasActive = $this->subscriptionService->checkActive($request->user());
        $subscription = $this->subscriptionService->getCurrent($request->user());

        return response([
            'data' => [
                'has_active_subscription' => $hasActive,
                'subscription' => $subscription ? new SubscriptionResource($subscription) : null,
            ],
        ]);
    }
}
