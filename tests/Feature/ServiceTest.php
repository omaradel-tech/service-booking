<?php

use App\Models\User;
use App\Modules\Service\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

uses(RefreshDatabase::class);

test('user can list active services', function () {
    Service::factory()->count(5)->create(['is_active' => true]);
    Service::factory()->count(2)->create(['is_active' => false]);

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

    $this->assertEquals(5, count($response->json('data')));
});

test('user can get service details', function () {
    $service = Service::factory()->create([
        'name' => 'Test Service',
        'price' => 150.00,
        'duration_minutes' => 120,
    ]);

    $response = $this->getJson("/api/v1/services/{$service->id}");

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
                'price' => 150.00,
                'duration_minutes' => 120,
            ],
        ]);
});

test('user gets 404 for non-existent service', function () {
    $response = $this->getJson('/api/v1/services/999');

    $response->assertStatus(404)
        ->assertJson([
            'error' => [
                'code' => 'NOT_FOUND',
                'message' => 'Service not found',
            ],
        ]);
});

test('service factory creates valid service', function () {
    $service = Service::factory()->create();

    expect($service)->toBeInstanceOf(Service::class);
    expect($service->name)->toBeString();
    expect($service->price)->toBeString(); // Database returns string
    expect($service->duration_minutes)->toBeInt();
    expect($service->is_active)->toBeBool();
});

test('service factory can create inactive service', function () {
    $service = Service::factory()->inactive()->create();

    expect($service->is_active)->toBeFalse();
});

test('service factory can create service with specific price', function () {
    $price = 250.50;
    $service = Service::factory()->withPrice($price)->create();

    expect((float)$service->price)->toBe($price); // Convert to float for comparison
});

test('service factory can create service with specific duration', function () {
    $duration = 180;
    $service = Service::factory()->withDuration($duration)->create();

    expect($service->duration_minutes)->toBe($duration);
});
