<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

uses(RefreshDatabase::class);

test('user can register with valid data', function () {
    $userData = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ];

    $response = $this->postJson('/api/v1/auth/register', $userData);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'email',
                'created_at',
            ],
            'token',
            'message',
        ])
        ->assertJson([
            'data' => [
                'name' => 'John Doe',
                'email' => 'john@example.com',
            ],
            'message' => 'User registered successfully',
        ]);

    $this->assertDatabaseHas('users', [
        'email' => 'john@example.com',
    ]);

    $this->assertDatabaseHas('subscriptions', [
        'user_id' => 1,
        'type' => 'trial',
        'status' => 'active',
    ]);
});

test('user cannot register with invalid email', function () {
    $userData = [
        'name' => 'John Doe',
        'email' => 'invalid-email',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ];

    $response = $this->postJson('/api/v1/auth/register', $userData);

    $response->assertStatus(422)
        ->assertJson([
            'error' => [
                'code' => 'VALIDATION_ERROR',
                'message' => 'The given data was invalid.',
            ],
        ])
        ->assertJsonStructure([
            'error' => [
                'details' => [
                    'email',
                ],
            ],
        ]);
});

test('user cannot register with duplicate email', function () {
    User::factory()->create(['email' => 'john@example.com']);

    $userData = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ];

    $response = $this->postJson('/api/v1/auth/register', $userData);

    $response->assertStatus(422)
        ->assertJson([
            'error' => [
                'code' => 'VALIDATION_ERROR',
                'message' => 'The given data was invalid.',
            ],
        ])
        ->assertJsonStructure([
            'error' => [
                'details' => [
                    'email',
                ],
            ],
        ]);
});

test('user can login with valid credentials', function () {
    $user = User::factory()->create([
        'password' => bcrypt('password123'),
    ]);

    $loginData = [
        'email' => $user->email,
        'password' => 'password123',
    ];

    $response = $this->postJson('/api/v1/auth/login', $loginData);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'email',
            ],
            'token',
            'message',
        ])
        ->assertJson([
            'message' => 'Login successful',
        ]);
});

test('user cannot login with invalid credentials', function () {
    $user = User::factory()->create([
        'password' => bcrypt('password123'),
    ]);

    $loginData = [
        'email' => $user->email,
        'password' => 'wrongpassword',
    ];

    $response = $this->postJson('/api/v1/auth/login', $loginData);

    $response->assertStatus(401)
        ->assertJson([
            'message' => 'Invalid credentials',
        ]);
});

test('user can logout', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/auth/logout');

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Logout successful',
        ]);

    $this->assertDatabaseMissing('personal_access_tokens', [
        'tokenable_id' => $user->id,
    ]);
});

test('user can get their profile', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/auth/me');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'email',
                'created_at',
                'updated_at',
            ],
        ])
        ->assertJson([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
});

test('unauthenticated user cannot access protected routes', function () {
    $response = $this->getJson('/api/v1/auth/me');

    $response->assertStatus(401);
});
