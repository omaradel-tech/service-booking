<?php

use App\Modules\Service\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(RefreshDatabase::class);

test('can access services endpoint', function () {
    // First, let's just test if the endpoint exists and returns proper structure
    $response = $this->getJson('/api/v1/services');

    // Even with no services, it should return 200 with empty array
    $response->assertStatus(200)
        ->assertJsonStructure([
            'data',
        ]);
});

test('can create service and list it', function () {
    // Create a service directly in the database
    $service = Service::create([
        'name' => 'Test Service',
        'description' => 'Test Description',
        'price' => 100.00,
        'duration_minutes' => 60,
        'is_active' => true,
    ]);

    // Test the API endpoint
    $response = $this->getJson('/api/v1/services');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'description',
                    'price',
                    'duration_minutes',
                    'is_active',
                    'created_at',
                    'updated_at',
                ],
            ],
        ]);

    // Check if our service is in the response
    $data = $response->json('data');
    expect($data)->toHaveCount(1);
    expect($data[0]['name'])->toBe('Test Service');
});
