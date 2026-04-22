<?php

namespace App\Modules\Subscription\Observers;

use App\Modules\Subscription\Models\Subscription;
use Illuminate\Support\Facades\Log;

class SubscriptionObserver
{
    /**
     * Handle the Subscription "created" event.
     */
    public function created(Subscription $subscription): void
    {
        Log::info('Subscription created', [
            'subscription_id' => $subscription->id,
            'user_id' => $subscription->user_id,
            'type' => $subscription->type,
            'status' => $subscription->status,
        ]);

        // Could dispatch jobs for welcome emails, trial notifications, etc.
        if ($subscription->type === 'trial') {
            // Send trial welcome email
        }
    }

    /**
     * Handle the Subscription "updated" event.
     */
    public function updated(Subscription $subscription): void
    {
        if ($subscription->wasChanged('status')) {
            Log::info('Subscription status changed', [
                'subscription_id' => $subscription->id,
                'user_id' => $subscription->user_id,
                'old_status' => $subscription->getOriginal('status'),
                'new_status' => $subscription->status,
            ]);

            // Handle status change events
            if ($subscription->status === 'expired') {
                // Send expiration notification
                // Cancel future bookings if needed
            } elseif ($subscription->status === 'canceled') {
                // Send cancellation confirmation
                // Handle grace period logic
            }
        }
    }

    /**
     * Handle the Subscription "deleted" event.
     */
    public function deleted(Subscription $subscription): void
    {
        Log::info('Subscription deleted', [
            'subscription_id' => $subscription->id,
            'user_id' => $subscription->user_id,
        ]);
    }
}
