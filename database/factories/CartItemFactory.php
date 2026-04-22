<?php

namespace Database\Factories;

use App\Core\Domain\Enums\CartItemType;
use App\Modules\Cart\Models\CartItem;
use App\Modules\Cart\Models\Cart;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Modules\Cart\Models\CartItem>
 */
class CartItemFactory extends Factory
{
    protected $model = CartItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cart_id' => Cart::factory(),
            'item_type' => $this->faker->randomElement([CartItemType::SERVICE(), CartItemType::PACKAGE()]),
            'item_id' => $this->faker->numberBetween(1, 100),
            'quantity' => $this->faker->numberBetween(1, 5),
        ];
    }

    /**
     * Create a service cart item.
     */
    public function service(): static
    {
        return $this->state(fn (array $attributes) => [
            'item_type' => CartItemType::SERVICE(),
        ]);
    }

    /**
     * Create a package cart item.
     */
    public function package(): static
    {
        return $this->state(fn (array $attributes) => [
            'item_type' => CartItemType::PACKAGE(),
        ]);
    }

    /**
     * Create a cart item for a specific cart.
     */
    public function forCart(int $cartId): static
    {
        return $this->state(fn (array $attributes) => [
            'cart_id' => $cartId,
        ]);
    }

    /**
     * Create a cart item for a specific item ID.
     */
    public function forItem(int $itemId): static
    {
        return $this->state(fn (array $attributes) => [
            'item_id' => $itemId,
        ]);
    }

    /**
     * Create a cart item with specific quantity.
     */
    public function withQuantity(int $quantity): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => $quantity,
        ]);
    }
}
