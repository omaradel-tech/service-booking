<?php

namespace App\Core\Infrastructure\Repositories;

use App\Core\Application\Contracts\SubscriptionRepositoryInterface;
use App\Core\Domain\Enums\SubscriptionStatus;
use App\Models\User;
use App\Modules\Subscription\Models\Subscription;

class EloquentSubscriptionRepository implements SubscriptionRepositoryInterface
{
    /**
     * Get active subscription for user.
     */
    public function getActiveForUser(User $user): ?Subscription
    {
        return Subscription::where('user_id', $user->id)
            ->whereIn('status', [SubscriptionStatus::ACTIVE(), SubscriptionStatus::EXPIRED()])
            ->orderBy('created_at', 'desc')
            ->first();
    }

    /**
     * Create a new subscription.
     */
    public function create(array $data): Subscription
    {
        return Subscription::create($data);
    }

    /**
     * Expire a subscription.
     */
    public function expire(Subscription $subscription): bool
    {
        return $subscription->update([
            'status' => SubscriptionStatus::EXPIRED(),
        ]);
    }

    /**
     * Find subscription by ID.
     */
    public function find(int $id): ?Subscription
    {
        return Subscription::find($id);
    }

    /**
     * Update subscription.
     */
    public function update(Subscription $subscription, array $data): bool
    {
        return $subscription->update($data);
    }

    /**
     * Delete subscription.
     */
    public function delete(Subscription $subscription): bool
    {
        return $subscription->delete();
    }
}
