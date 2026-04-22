<?php

use App\Modules\Service\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(RefreshDatabase::class);

test('can access services endpoint', function () {
    // Use the Laravel HTTP test methods
    $response = $this->json('GET', '/api/v1/services');

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
    $response = $this->json('GET', '/api/v1/services');

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

test('can get service details', function () {
    // Create a service directly in the database
    $service = Service::create([
        'name' => 'Test Service',
        'description' => 'Test Description',
        'price' => 100.00,
        'duration_minutes' => 60,
        'is_active' => true,
    ]);

    // Test the API endpoint
    $response = $this->json('GET', "/api/v1/services/{$service->id}");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'description',
                'price',
                'duration_minutes',
                'is_active',
                'created_at',
                'updated_at',
            ],
        ])
        ->assertJson([
            'data' => [
                'id' => $service->id,
                'name' => 'Test Service',
                'price' => 100.00,
                'duration_minutes' => 60,
            ],
        ]);
});
