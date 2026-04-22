<?php

namespace App\Core\Application\Services;

use App\Core\Application\Contracts\SubscriptionRepositoryInterface;
use App\Core\Domain\Enums\SubscriptionStatus;
use App\Core\Domain\Enums\SubscriptionType;
use App\Models\User;
use App\Modules\Subscription\Models\Subscription;
use Illuminate\Support\Facades\Log;

class SubscriptionService
{
    public function __construct(
        private SubscriptionRepositoryInterface $subscriptionRepository
    ) {}

    /**
     * Create a trial subscription for user.
     */
    public function createTrial(User $user): Subscription
    {
        // Check if user already has an active subscription
        $existingSubscription = $this->subscriptionRepository->getActiveForUser($user);
        if ($existingSubscription) {
            throw new \Exception('User already has an active subscription');
        }

        $trialData = [
            'user_id' => $user->id,
            'type' => SubscriptionType::TRIAL(),
            'status' => SubscriptionStatus::ACTIVE(),
            'starts_at' => now(),
            'ends_at' => now()->addDays(30), // 30-day trial
            'grace_ends_at' => now()->addDays(37), // 7-day grace period
        ];

        $subscription = $this->subscriptionRepository->create($trialData);
        
        Log::info('Trial subscription created', [
            'user_id' => $user->id,
            'subscription_id' => $subscription->id,
        ]);

        return $subscription;
    }

    /**
     * Check if user has active subscription.
     */
    public function checkActive(User $user): bool
    {
        $subscription = $this->subscriptionRepository->getActiveForUser($user);
        
        if (!$subscription) {
            return false;
        }

        return $subscription->isActive() || $subscription->isInGracePeriod();
    }

    /**
     * Expire overdue subscriptions.
     */
    public function expireOverdue(): int
    {
        $expiredCount = 0;
        
        // This would typically be called by a scheduled job
        // For now, we'll implement a basic version that finds expired subscriptions
        
        Log::info('Checking for overdue subscriptions to expire');
        
        // In a real implementation, this would query the repository for expired subscriptions
        // and update their status to EXPIRED
        
        return $expiredCount;
    }

    /**
     * Cancel subscription.
     */
    public function cancel(User $user): bool
    {
        $subscription = $this->subscriptionRepository->getActiveForUser($user);
        
        if (!$subscription) {
            throw new \Exception('No active subscription found');
        }

        $updated = $this->subscriptionRepository->update($subscription, [
            'status' => SubscriptionStatus::CANCELED(),
        ]);

        if ($updated) {
            Log::info('Subscription canceled', [
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
            ]);
        }

        return $updated;
    }

    /**
     * Get current subscription for user.
     */
    public function getCurrent(User $user): ?Subscription
    {
        return $this->subscriptionRepository->getActiveForUser($user);
    }
}
