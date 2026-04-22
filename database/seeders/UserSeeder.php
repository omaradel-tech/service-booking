<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create demo user
        User::factory()->create([
            'name' => 'Demo User',
            'email' => 'demo@ajeer.app',
        ]);

        // Create additional test users
        User::factory(10)->create();

        $this->command->info('Demo user created: demo@ajeer.app / password');
    }
}
