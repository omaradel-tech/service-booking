<?php

use App\Models\User;
use App\Modules\Service\Models\Service;
use App\Modules\Package\Models\Package;
use App\Core\Domain\Enums\CartItemType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

uses(RefreshDatabase::class);

test('can add service to cart', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $service = Service::factory()->create();

    // Test direct CartItem creation first
    $cart = \App\Modules\Cart\Models\Cart::create(['user_id' => $user->id]);
    $cartItem = new \App\Modules\Cart\Models\CartItem();
    $cartItem->cart_id = $cart->id;
    $cartItem->item_type = 'service';
    $cartItem->item_id = $service->id;
    $cartItem->quantity = 1;
    $cartItem->save();

    $this->assertNotNull($cartItem->id);
    $this->assertEquals('service', $cartItem->item_type);

    $response = $this->postJson('/api/v1/cart/items', [
        'item_type' => 'service',
        'item_id' => $service->id,
        'quantity' => 1,
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'data' => [
                'id',
                'item_type',
                'item_id',
                'quantity',
                'total_price',
            ],
            'message',
        ]);
});

test('can add package to cart', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $package = Package::factory()->create();

    $response = $this->postJson('/api/v1/cart/items', [
        'item_type' => 'package',
        'item_id' => $package->id,
        'quantity' => 1,
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'data' => [
                'id',
                'item_type',
                'item_id',
                'quantity',
                'total_price',
            ],
            'message',
        ]);
});

test('can view cart with total price', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $service = Service::factory()->create(['price' => 100.00]);
    $package = Package::factory()->create(['price' => 200.00]);

    // Add items to cart
    $this->postJson('/api/v1/cart/items', [
        'item_type' => 'service',
        'item_id' => $service->id,
        'quantity' => 2,
    ]);

    $this->postJson('/api/v1/cart/items', [
        'item_type' => 'package',
        'item_id' => $package->id,
        'quantity' => 1,
    ]);

    // Get cart
    $response = $this->getJson('/api/v1/cart');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'id',
                'user_id',
                'total_price',
                'items',
            ],
        ])
        ->assertJsonPath('data.total_price', 400); // 2 * 100 + 1 * 200
});
