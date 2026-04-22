<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Modules\Cart\Models\Cart;
use App\Modules\Cart\Models\CartItem;
use App\Core\Application\Services\CartService;
use App\Core\Application\DTOs\AddCartItemDTO;
use App\Modules\Service\Models\Service;
use App\Modules\Package\Models\Package;
use App\Core\Domain\Enums\CartItemType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartServiceTest extends TestCase
{
    use RefreshDatabase;

    private CartService $cartService;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cartService = app(CartService::class);
        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_can_add_service_to_cart()
    {
        $service = Service::factory()->create();

        $dto = new AddCartItemDTO(
            user: $this->user,
            itemType: CartItemType::SERVICE,
            itemId: $service->id,
            quantity: 1
        );

        $cartItem = $this->cartService->addItem($dto);

        $this->assertInstanceOf(CartItem::class, $cartItem);
        $this->assertEquals($service->id, $cartItem->item_id);
        $this->assertEquals(CartItemType::SERVICE, $cartItem->item_type);
        $this->assertEquals(1, $cartItem->quantity);
        $this->assertEquals($this->user->id, $cartItem->cart->user_id);
    }

    /** @test */
    public function it_can_add_package_to_cart()
    {
        $package = Package::factory()->create();

        $dto = new AddCartItemDTO(
            user: $this->user,
            itemType: CartItemType::PACKAGE,
            itemId: $package->id,
            quantity: 1
        );

        $cartItem = $this->cartService->addItem($dto);

        $this->assertInstanceOf(CartItem::class, $cartItem);
        $this->assertEquals($package->id, $cartItem->item_id);
        $this->assertEquals(CartItemType::PACKAGE, $cartItem->item_type);
        $this->assertEquals(1, $cartItem->quantity);
    }

    /** @test */
    public function it_can_remove_item_from_cart()
    {
        $service = Service::factory()->create();

        $dto = new AddCartItemDTO(
            user: $this->user,
            itemType: CartItemType::SERVICE,
            itemId: $service->id,
            quantity: 1
        );

        $cartItem = $this->cartService->addItem($dto);

        $removed = $this->cartService->removeItem($this->user, CartItemType::SERVICE, $service->id);

        $this->assertTrue($removed);
        $this->assertDatabaseMissing('cart_items', ['id' => $cartItem->id]);
    }

    /** @test */
    public function it_can_update_item_quantity()
    {
        $service = Service::factory()->create();

        $dto = new AddCartItemDTO(
            user: $this->user,
            itemType: CartItemType::SERVICE,
            itemId: $service->id,
            quantity: 1
        );

        $cartItem = $this->cartService->addItem($dto);
        $updated = $this->cartService->updateQuantity($cartItem, 3);

        $this->assertTrue($updated);
        $this->assertEquals(3, $cartItem->fresh()->quantity);
    }

    /** @test */
    public function it_can_get_user_cart()
    {
        $cart = $this->cartService->getCart($this->user);

        $this->assertInstanceOf(Cart::class, $cart);
        $this->assertEquals($this->user->id, $cart->user_id);
    }

    /** @test */
    public function it_can_clear_cart()
    {
        $service = Service::factory()->create();

        $dto = new AddCartItemDTO(
            user: $this->user,
            itemType: CartItemType::SERVICE,
            itemId: $service->id,
            quantity: 1
        );

        $this->cartService->addItem($dto);
        $cleared = $this->cartService->clearCart($this->user);

        $this->assertTrue($cleared);
        $this->assertDatabaseCount('cart_items', 0);
    }

    /** @test */
    public function it_can_get_total_price()
    {
        $service1 = Service::factory()->create(['price' => 100]);
        $service2 = Service::factory()->create(['price' => 50]);

        $dto1 = new AddCartItemDTO(
            user: $this->user,
            itemType: CartItemType::SERVICE,
            itemId: $service1->id,
            quantity: 2
        );

        $dto2 = new AddCartItemDTO(
            user: $this->user,
            itemType: CartItemType::SERVICE,
            itemId: $service2->id,
            quantity: 1
        );

        $this->cartService->addItem($dto1);
        $this->cartService->addItem($dto2);

        $totalPrice = $this->cartService->getTotalPrice($this->user);
        $this->assertEquals(250, $totalPrice); // (100 * 2) + 50
    }

    /** @test */
    public function it_can_get_items_count()
    {
        $service = Service::factory()->create();

        $dto = new AddCartItemDTO(
            user: $this->user,
            itemType: CartItemType::SERVICE,
            itemId: $service->id,
            quantity: 3
        );

        $this->cartService->addItem($dto);

        $itemsCount = $this->cartService->getItemsCount($this->user);
        $this->assertEquals(3, $itemsCount);
    }
}
