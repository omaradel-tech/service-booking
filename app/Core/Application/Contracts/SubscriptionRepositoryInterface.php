<?php

namespace App\Core\Application\Contracts;

use App\Models\User;
use App\Modules\Subscription\Models\Subscription;

interface SubscriptionRepositoryInterface
{
    /**
     * Get active subscription for user.
     */
    public function getActiveForUser(User $user): ?Subscription;

    /**
     * Create a new subscription.
     */
    public function create(array $data): Subscription;

    /**
     * Expire a subscription.
     */
    public function expire(Subscription $subscription): bool;

    /**
     * Find subscription by ID.
     */
    public function find(int $id): ?Subscription;

    /**
     * Update subscription.
     */
    public function update(Subscription $subscription, array $data): bool;

    /**
     * Delete subscription.
     */
    public function delete(Subscription $subscription): bool;

    /**
     * Expire overdue subscriptions.
     */
    public function expireOverdue(): int;
}
