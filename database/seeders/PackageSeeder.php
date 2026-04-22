<?php

namespace Database\Seeders;

use App\Modules\Package\Models\Package;
use App\Modules\Service\Models\Service;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $packages = [
            [
                'name' => 'Basic Home Maintenance',
                'price' => 300.00,
                'is_active' => true,
                'services' => ['Plumbing Service', 'Electrical Maintenance'],
            ],
            [
                'name' => 'Complete Home Care',
                'price' => 550.00,
                'is_active' => true,
                'services' => ['AC Repair', 'Plumbing Service', 'Electrical Maintenance', 'HVAC Inspection'],
            ],
            [
                'name' => 'Premium Home Package',
                'price' => 750.00,
                'is_active' => true,
                'services' => ['AC Repair', 'Plumbing Service', 'Electrical Maintenance', 'HVAC Inspection', 'Appliance Repair'],
            ],
            [
                'name' => 'Seasonal Maintenance',
                'price' => 400.00,
                'is_active' => true,
                'services' => ['HVAC Inspection', 'Roof Inspection', 'Pest Control'],
            ],
            [
                'name' => 'Appliance Care Package',
                'price' => 180.00,
                'is_active' => true,
                'services' => ['Appliance Repair', 'Electrical Maintenance'],
            ],
        ];

        foreach ($packages as $packageData) {
            $services = $packageData['services'];
            unset($packageData['services']);

            $package = Package::create($packageData);

            // Attach services to package
            foreach ($services as $serviceName) {
                $service = Service::where('name', $serviceName)->first();
                if ($service) {
                    $package->services()->attach($service->id);
                }
            }
        }

        $this->command->info('Packages seeded successfully.');
    }
}
