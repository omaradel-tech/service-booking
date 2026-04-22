<?php

namespace App\Modules\Subscription\Jobs;

use App\Core\Application\Contracts\SubscriptionRepositoryInterface;
use App\Core\Domain\Enums\SubscriptionStatus;
use App\Modules\Subscription\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ExpireSubscriptionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Subscription $subscription
    ) {}

    /**
     * Execute the job.
     */
    public function handle(SubscriptionRepositoryInterface $subscriptionRepository): void
    {
        try {
            $subscriptionRepository->update($this->subscription, [
                'status' => SubscriptionStatus::EXPIRED(),
            ]);

            Log::info('Subscription expired successfully', [
                'subscription_id' => $this->subscription->id,
                'user_id' => $this->subscription->user_id,
            ]);

            // Could dispatch additional jobs for notifications, cleanup, etc.
        } catch (\Exception $e) {
            Log::error('Failed to expire subscription', [
                'subscription_id' => $this->subscription->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
