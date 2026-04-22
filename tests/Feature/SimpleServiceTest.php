<?php

use App\Modules\Service\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(RefreshDatabase::class);

test('can create service manually', function () {
    $service = Service::create([
        'name' => 'Test Service',
        'description' => 'Test Description',
        'price' => 100.00,
        'duration_minutes' => 60,
        'is_active' => true,
    ]);

    expect($service)->toBeInstanceOf(Service::class);
    expect($service->name)->toBe('Test Service');
    expect($service->price)->toBe('100.00');
});

test('can list services via API', function () {
    Service::create([
        'name' => 'Test Service',
        'description' => 'Test Description',
        'price' => 100.00,
        'duration_minutes' => 60,
        'is_active' => true,
    ]);

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
});
