<?php

namespace Database\Seeders;

use App\Models\User;
use App\Modules\Booking\Models\Booking;
use App\Modules\Service\Models\Service;
use App\Core\Domain\Enums\BookingStatus;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
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

        $services = Service::take(5)->get();

        if ($services->isEmpty()) {
            $this->command->error('No services found. Please run ServiceSeeder first.');
            return;
        }

        // Create a future confirmed booking for demo user
        Booking::create([
            'user_id' => $demoUser->id,
            'service_id' => $services->first()->id,
            'scheduled_at' => now()->addDays(2)->setHour(10)->setMinute(0)->setSecond(0),
            'status' => BookingStatus::CONFIRMED,
        ]);

        // Create a past completed booking for demo user
        Booking::create([
            'user_id' => $demoUser->id,
            'service_id' => $services->skip(1)->first()->id,
            'scheduled_at' => now()->subDays(5)->setHour(14)->setMinute(0)->setSecond(0),
            'status' => BookingStatus::COMPLETED,
        ]);

        // Create some bookings for other users
        $otherUsers = User::where('email', '!=', 'demo@ajeer.app')->limit(5)->get();
        
        foreach ($otherUsers as $user) {
            Booking::create([
                'user_id' => $user->id,
                'service_id' => $services->random()->id,
                'scheduled_at' => now()->addDays(rand(1, 15))->setHour(rand(9, 17))->setMinute(0)->setSecond(0),
                'status' => BookingStatus::PENDING,
            ]);
        }

        $this->command->info('Bookings seeded successfully.');
    }
}
