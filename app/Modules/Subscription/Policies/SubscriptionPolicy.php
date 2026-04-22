<?php

namespace App\Modules\Subscription\Policies;

use App\Models\User;
use App\Modules\Subscription\Models\Subscription;

class SubscriptionPolicy
{
    /**
     * Determine if the user can view the subscription.
     */
    public function view(User $user, Subscription $subscription): bool
    {
        return $user->id === $subscription->user_id;
    }

    /**
     * Determine if the user can update the subscription.
     */
    public function update(User $user, Subscription $subscription): bool
    {
        return $user->id === $subscription->user_id;
    }

    /**
     * Determine if the user can cancel the subscription.
     */
    public function cancel(User $user, Subscription $subscription): bool
    {
        return $user->id === $subscription->user_id && $subscription->isActive();
    }

    /**
     * Determine if the user can create subscriptions.
     */
    public function create(User $user): bool
    {
        return true; // All authenticated users can create subscriptions
    }
}
