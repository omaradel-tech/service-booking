<?php

namespace Database\Seeders;

use App\Models\User;
use App\Modules\Subscription\Models\Subscription;
use App\Core\Domain\Enums\SubscriptionStatus;
use App\Core\Domain\Enums\SubscriptionType;
use Illuminate\Database\Seeder;

class SubscriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $demoUser = User::where('email', 'demo@ajeer.app')->first();
        
        if (!$demoUser) {
            $this->command->error('Demo user not found. Please run UserSeeder first.');
            return;
        }

        // Create active trial subscription for demo user
        Subscription::create([
            'user_id' => $demoUser->id,
            'type' => SubscriptionType::TRIAL,
            'status' => SubscriptionStatus::ACTIVE,
            'starts_at' => now(),
            'ends_at' => now()->addDays(30),
            'grace_ends_at' => now()->addDays(37),
        ]);

        // Create some subscriptions for other users
        $users = User::where('email', '!=', 'demo@ajeer.app')->limit(5)->get();
        
        foreach ($users as $user) {
            Subscription::create([
                'user_id' => $user->id,
                'type' => SubscriptionType::TRIAL,
                'status' => SubscriptionStatus::ACTIVE,
                'starts_at' => now()->subDays(rand(1, 20)),
                'ends_at' => now()->addDays(rand(10, 30)),
                'grace_ends_at' => now()->addDays(rand(37, 45)),
            ]);
        }

        $this->command->info('Subscriptions seeded successfully.');
    }
}
