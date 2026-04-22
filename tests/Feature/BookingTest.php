<?php

use App\Models\User;
use App\Modules\Booking\Models\Booking;
use App\Modules\Service\Models\Service;
use App\Core\Domain\Enums\BookingStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

uses(RefreshDatabase::class);

test('user can create booking with active subscription', function () {
    // Register a new user which automatically creates a trial subscription
    $registerResponse = $this->postJson('/api/v1/auth/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);
    
    $registerResponse->assertStatus(201);
    
    // Use the token from registration to authenticate
    $token = $registerResponse->json('token');
    $this->withHeader('Authorization', 'Bearer ' . $token);

    $service = Service::factory()->create();

    $bookingData = [
        'service_id' => $service->id,
        'scheduled_at' => now()->addDays(1)->toIso8601String(),
    ];

    $response = $this->postJson('/api/v1/bookings', $bookingData);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'data' => [
                'id',
                'user_id',
                'service_id',
                'scheduled_at',
                'status',
                'service',
                'can_be_canceled',
            ],
            'message',
        ])
        ->assertJson([
            'message' => 'Booking created successfully',
        ]);

    $this->assertDatabaseHas('bookings', [
        'user_id' => $registerResponse->json('data.id'),
        'service_id' => $service->id,
        'status' => BookingStatus::PENDING(),
    ]);
});

test('user cannot create booking without active subscription', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $service = Service::factory()->create();

    $bookingData = [
        'service_id' => $service->id,
        'scheduled_at' => now()->addDays(1)->toIso8601String(),
    ];

    $response = $this->postJson('/api/v1/bookings', $bookingData);

    $response->assertStatus(400)
        ->assertJson([
            'message' => 'Active subscription required to create booking',
        ]);
});

test('user can list their bookings', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    // Create trial subscription
    $this->postJson('/api/v1/auth/register', [
        'name' => $user->name,
        'email' => $user->email,
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $service = Service::factory()->create();
    
    Booking::factory()->count(3)->create([
        'user_id' => $user->id,
        'service_id' => $service->id,
    ]);

    // Create bookings for other user
    $otherUser = User::factory()->create();
    Booking::factory()->count(2)->create([
        'user_id' => $otherUser->id,
        'service_id' => $service->id,
    ]);

    $response = $this->getJson('/api/v1/bookings');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'user_id',
                    'service_id',
                    'scheduled_at',
                    'status',
                    'service',
                    'can_be_canceled',
                ],
            ],
        ]);

    $this->assertEquals(3, count($response->json('data')));
});

test('user can cancel their booking', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    // Create trial subscription
    $this->postJson('/api/v1/auth/register', [
        'name' => $user->name,
        'email' => $user->email,
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $service = Service::factory()->create();
    
    $booking = Booking::factory()->create([
        'user_id' => $user->id,
        'service_id' => $service->id,
        'scheduled_at' => now()->addDays(1),
        'status' => BookingStatus::PENDING(),
    ]);

    $response = $this->patchJson("/api/v1/bookings/{$booking->id}/cancel");

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Booking canceled successfully',
        ]);

    $this->assertDatabaseHas('bookings', [
        'id' => $booking->id,
        'status' => BookingStatus::CANCELED(),
    ]);
});

test('user cannot cancel booking that cannot be canceled', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    // Create trial subscription
    $this->postJson('/api/v1/auth/register', [
        'name' => $user->name,
        'email' => $user->email,
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $service = Service::factory()->create();
    
    $booking = Booking::factory()->create([
        'user_id' => $user->id,
        'service_id' => $service->id,
        'scheduled_at' => now()->subDays(1), // Past booking
        'status' => BookingStatus::COMPLETED(),
    ]);

    $response = $this->patchJson("/api/v1/bookings/{$booking->id}/cancel");

    $response->assertStatus(400)
        ->assertJson([
            'message' => 'Booking cannot be canceled',
        ]);
});

test('booking validation fails with invalid data', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    // Create trial subscription
    $this->postJson('/api/v1/auth/register', [
        'name' => $user->name,
        'email' => $user->email,
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $invalidBookingData = [
        'service_id' => 999, // Non-existent service
        'scheduled_at' => now()->subDays(1)->toIso8601String(), // Past time
    ];

    $response = $this->postJson('/api/v1/bookings', $invalidBookingData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['service_id', 'scheduled_at']);
});

test('booking factory creates valid booking', function () {
    $booking = Booking::factory()->create();

    expect($booking)->toBeInstanceOf(Booking::class);
    expect($booking->user_id)->toBeInt();
    expect($booking->service_id)->toBeInt();
    expect($booking->scheduled_at)->toBeInstanceOf(\Carbon\Carbon::class);
    expect($booking->status)->toBeInstanceOf(BookingStatus::class);
    expect($booking->status->getValue())->toBeString();
});

test('booking factory can create booking with specific status', function () {
    $booking = Booking::factory()->confirmed()->create();

    expect($booking->status->getValue())->toBe(BookingStatus::CONFIRMED);
});

test('booking factory can create booking for specific user', function () {
    $user = User::factory()->create();
    $booking = Booking::factory()->forUser($user->id)->create();

    expect($booking->user_id)->toBe($user->id);
});
