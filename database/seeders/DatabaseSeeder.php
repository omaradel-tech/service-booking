<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            ServiceSeeder::class,
            PackageSeeder::class,
            UserSeeder::class,
            SubscriptionSeeder::class,
            BookingSeeder::class,
            CartSeeder::class,
        ]);

        $this->command->info('Database seeding completed!');
        $this->command->info('Demo credentials:');
        $this->command->info('Email: demo@ajeer.app');
        $this->command->info('Password: password');
        $this->command->info('');
        $this->command->info('Demo data includes:');
        $this->command->info('- 1 demo user with active trial subscription');
        $this->command->info('- 2 bookings (1 future confirmed, 1 past completed)');
        $this->command->info('- 1 cart with service and package items');
        $this->command->info('- 10 additional test users with subscriptions and bookings');
    }
}
