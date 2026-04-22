<?php

namespace App\Console\Commands;

use App\Core\Application\Services\SubscriptionService;
use Illuminate\Console\Command;

class ExpireOverdueSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:expire-overdue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire overdue subscriptions that are past their grace period';

    /**
     * Execute the console command.
     */
    public function handle(SubscriptionService $subscriptionService): int
    {
        $this->info('Starting subscription expiry process...');

        try {
            $expiredCount = $subscriptionService->expireOverdue();

            if ($expiredCount > 0) {
                $this->info("Successfully expired {$expiredCount} overdue subscriptions.");
            } else {
                $this->info('No overdue subscriptions found.');
            }

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error during subscription expiry: ' . $e->getMessage());
            
            return self::FAILURE;
        }
    }
}
