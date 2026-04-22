<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Modules\Cart\Models\Cart;
use App\Modules\Cart\Models\CartItem;
use App\Modules\Cart\Services\CheckoutService;
use App\Modules\Cart\DTOs\CheckoutDTO;
use App\Modules\Cart\DTOs\CheckoutItemScheduleDTO;
use App\Modules\Booking\Models\Booking;
use App\Modules\Service\Models\Service;
use App\Modules\Package\Models\Package;
use App\Modules\Subscription\Models\Subscription;
use App\Core\Domain\Enums\CartItemType;
use App\Core\Domain\Enums\BookingStatus;
use App\Core\Domain\Enums\SubscriptionStatus;
use App\Core\Domain\Enums\SubscriptionType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutServiceTest extends TestCase
{
    use RefreshDatabase;

    private CheckoutService $checkoutService;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->checkoutService = app(CheckoutService::class);
        $this->user = User::factory()->create();
        
        // Create active subscription for user
        Subscription::factory()->create([
            'user_id' => $this->user->id,
            'type' => SubscriptionType::TRIAL,
            'status' => SubscriptionStatus::ACTIVE,
            'starts_at' => now(),
            'ends_at' => now()->addDays(30),
        ]);
    }

    /** @test */
    public function it_can_checkout_single_service()
    {
        $service = Service::factory()->create();
        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'item_type' => CartItemType::SERVICE,
            'item_id' => $service->id,
            'quantity' => 1,
        ]);

        $schedules = [
            new CheckoutItemScheduleDTO(
                cart_item_id: $cart->items->first()->id,
                service_id: $service->id,
                scheduled_at: now()->addDays(2)
            ),
        ];

        $checkoutDTO = new CheckoutDTO(schedules: $schedules);

        $bookings = $this->checkoutService->checkout($this->user, $checkoutDTO);

        $this->assertCount(1, $bookings);
        $this->assertInstanceOf(Booking::class, $bookings->first());
        $this->assertEquals($service->id, $bookings->first()->service_id);
        $this->assertEquals($this->user->id, $bookings->first()->user_id);
        $this->assertEquals(BookingStatus::PENDING, $bookings->first()->status);
    }

    /** @test */
    public function it_can_checkout_package_with_multiple_services()
    {
        $service1 = Service::factory()->create();
        $service2 = Service::factory()->create();
        $package = Package::factory()->create();
        $package->services()->attach([$service1->id, $service2->id]);

        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'item_type' => CartItemType::PACKAGE,
            'item_id' => $package->id,
            'quantity' => 1,
        ]);

        $checkoutData = [
            'schedules' => [
                [
                    'cart_item_id' => $cart->items->first()->id,
                    'service_id' => $service1->id,
                    'scheduled_at' => now()->addDays(2)->toISOString(),
                ],
                [
                    'cart_item_id' => $cart->items->first()->id,
                    'service_id' => $service2->id,
                    'scheduled_at' => now()->addDays(3)->toISOString(),
                ],
            ],
        ];

        $bookings = $this->checkoutService->checkout($this->user, $checkoutData);

        $this->assertCount(2, $bookings);
        $this->assertEquals($service1->id, $bookings[0]->service_id);
        $this->assertEquals($service2->id, $bookings[1]->service_id);
    }

    /** @test */
    public function it_validates_service_belongs_to_cart_item()
    {
        $service1 = Service::factory()->create();
        $service2 = Service::factory()->create();
        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'item_type' => CartItemType::SERVICE,
            'item_id' => $service1->id,
            'quantity' => 1,
        ]);

        $checkoutData = [
            'schedules' => [
                [
                    'cart_item_id' => $cart->items->first()->id,
                    'service_id' => $service2->id, // Different service
                    'scheduled_at' => now()->addDays(2)->toISOString(),
                ],
            ],
        ];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Service does not belong to cart item');

        $this->checkoutService->checkout($this->user, $checkoutData);
    }

    /** @test */
    public function it_validates_scheduled_time_is_future()
    {
        $service = Service::factory()->create();
        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'item_type' => CartItemType::SERVICE,
            'item_id' => $service->id,
            'quantity' => 1,
        ]);

        $checkoutData = [
            'schedules' => [
                [
                    'cart_item_id' => $cart->items->first()->id,
                    'service_id' => $service->id,
                    'scheduled_at' => now()->subDay()->toISOString(), // Past time
                ],
            ],
        ];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Scheduled time must be in the future');

        $this->checkoutService->checkout($this->user, $checkoutData);
    }

    /** @test */
    public function it_clears_cart_after_successful_checkout()
    {
        $service = Service::factory()->create();
        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'item_type' => CartItemType::SERVICE,
            'item_id' => $service->id,
            'quantity' => 1,
        ]);

        $checkoutData = [
            'schedules' => [
                [
                    'cart_item_id' => $cart->items->first()->id,
                    'service_id' => $service->id,
                    'scheduled_at' => now()->addDays(2)->toISOString(),
                ],
            ],
        ];

        $this->checkoutService->checkout($this->user, $checkoutData);

        $this->assertDatabaseMissing('cart_items', ['cart_id' => $cart->id]);
    }

    /** @test */
    public function it_requires_active_subscription()
    {
        // Remove active subscription
        Subscription::where('user_id', $this->user->id)->delete();

        $service = Service::factory()->create();
        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'item_type' => CartItemType::SERVICE,
            'item_id' => $service->id,
            'quantity' => 1,
        ]);

        $checkoutData = [
            'schedules' => [
                [
                    'cart_item_id' => $cart->items->first()->id,
                    'service_id' => $service->id,
                    'scheduled_at' => now()->addDays(2)->toISOString(),
                ],
            ],
        ];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Active subscription required');

        $this->checkoutService->checkout($this->user, $checkoutData);
    }
}
