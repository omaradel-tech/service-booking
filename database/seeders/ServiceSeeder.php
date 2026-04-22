<?php

namespace Database\Seeders;

use App\Modules\Service\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = [
            [
                'name' => 'AC Repair',
                'description' => 'Complete air conditioning repair service including diagnostics and fixes',
                'price' => 150.00,
                'duration_minutes' => 120,
                'is_active' => true,
            ],
            [
                'name' => 'Plumbing Service',
                'description' => 'Professional plumbing repairs and installations',
                'price' => 120.00,
                'duration_minutes' => 90,
                'is_active' => true,
            ],
            [
                'name' => 'Electrical Maintenance',
                'description' => 'Comprehensive electrical system check and maintenance',
                'price' => 180.00,
                'duration_minutes' => 150,
                'is_active' => true,
            ],
            [
                'name' => 'HVAC Inspection',
                'description' => 'Thorough HVAC system inspection and tune-up',
                'price' => 100.00,
                'duration_minutes' => 60,
                'is_active' => true,
            ],
            [
                'name' => 'Appliance Repair',
                'description' => 'Repair service for household appliances',
                'price' => 95.00,
                'duration_minutes' => 75,
                'is_active' => true,
            ],
            [
                'name' => 'Roof Inspection',
                'description' => 'Professional roof inspection and minor repairs',
                'price' => 200.00,
                'duration_minutes' => 180,
                'is_active' => true,
            ],
            [
                'name' => 'Pest Control',
                'description' => 'Complete pest control and prevention service',
                'price' => 130.00,
                'duration_minutes' => 100,
                'is_active' => true,
            ],
            [
                'name' => 'Window Cleaning',
                'description' => 'Professional window cleaning service',
                'price' => 80.00,
                'duration_minutes' => 45,
                'is_active' => true,
            ],
        ];

        foreach ($services as $service) {
            Service::create($service);
        }

        $this->command->info('Services seeded successfully.');
    }
}
