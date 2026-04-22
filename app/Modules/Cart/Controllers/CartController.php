<?php

namespace App\Modules\Cart\Controllers;

use App\Core\Application\Contracts\CartRepositoryInterface;
use App\Core\Application\DTOs\AddCartItemDTO;
use App\Core\Application\Services\CartService;
use App\Http\Controllers\Controller;
use App\Modules\Cart\Requests\AddCartItemRequest;
use App\Modules\Cart\Requests\UpdateCartItemRequest;
use App\Modules\Cart\Resources\CartResource;
use App\Modules\Cart\Resources\CartItemResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * @group Cart
 * 
 * Cart management endpoints for adding, updating, and removing items from the shopping cart.
 * 
 * @authenticated
 */
class CartController extends Controller
{
    public function __construct(
        private CartService $cartService,
        private CartRepositoryInterface $cartRepository
    ) {}

    /**
     * Get user's cart
     * 
     * Returns the authenticated user's shopping cart with all items.
     * 
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "user_id": 1,
     *     "total_price": "300.00",
     *     "items": [
     *       {
     *         "id": 1,
     *         "item_type": "service",
     *         "item_id": 1,
     *         "quantity": 2,
     *         "item": {
     *           "id": 1,
     *           "name": "AC Repair",
     *           "price": "150.00"
     *         }
     *       }
     *     ]
     *   }
     * }
     */
    public function index(Request $request): Response
    {
        $cart = $this->cartService->getCart($request->user());

        return response([
            'data' => new CartResource($cart),
        ]);
    }

    /**
     * Add item to cart
     * 
     * Adds a service or package to the user's cart.
     * 
     * @bodyParam item_type string required Type of item (service or package). Example: service
     * @bodyParam item_id integer required ID of the service or package. Example: 1
     * @bodyParam quantity integer required Quantity of the item. Example: 2
     * 
     * @response 201 {
     *   "data": {
     *     "id": 1,
     *     "item_type": "service",
     *     "item_id": 1,
     *     "quantity": 2,
     *     "total_price": "300.00"
     *   },
     *   "message": "Item added to cart successfully"
     * }
     */
    public function addItem(AddCartItemRequest $request): Response
    {
        $dto = new AddCartItemDTO(
            user: $request->user(),
            itemType: $request->item_type,
            itemId: $request->item_id,
            quantity: $request->quantity
        );

        $cartItem = $this->cartService->addItem($dto);

        return response([
            'data' => new CartItemResource($cartItem),
            'message' => 'Item added to cart successfully',
        ], 201);
    }

    /**
     * Update cart item quantity
     * 
     * Updates the quantity of an existing cart item.
     * 
     * @urlParam id integer required Cart item ID. Example: 1
     * @bodyParam quantity integer required New quantity (must be at least 1). Example: 3
     * 
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "quantity": 3,
     *     "total_price": "450.00"
     *   },
     *   "message": "Cart item updated successfully"
     * }
     * @response 400 {
     *   "message": "Quantity must be at least 1"
     * }
     * @response 404 {
     *   "message": "Cart item not found"
     * }
     */
    public function updateItem(int $id, UpdateCartItemRequest $request): Response
    {
        $cartItem = $this->cartRepository->findItem($id);

        if (!$cartItem || $cartItem->cart->user_id !== $request->user()->id) {
            return response([
                'message' => 'Cart item not found',
            ], 404);
        }

        try {
            $this->cartService->updateQuantity($cartItem, $request->quantity);

            return response([
                'data' => new CartItemResource($cartItem->fresh()),
                'message' => 'Cart item updated successfully',
            ]);
        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Remove item from cart
     * 
     * Removes an item from the user's cart.
     * 
     * @urlParam id integer required Cart item ID. Example: 1
     * 
     * @response 200 {
     *   "message": "Item removed from cart successfully"
     * }
     * @response 404 {
     *   "message": "Cart item not found"
     * }
     */
    public function removeItem(int $id, Request $request): Response
    {
        $cartItem = $this->cartRepository->findItem($id);

        if (!$cartItem || $cartItem->cart->user_id !== $request->user()->id) {
            return response([
                'message' => 'Cart item not found',
            ], 404);
        }

        $removed = $this->cartService->removeItem($request->user(), $cartItem->item_type, $cartItem->item_id);

        if ($removed) {
            return response([
                'message' => 'Item removed from cart successfully',
            ]);
        }

        return response([
            'message' => 'Failed to remove item from cart',
        ], 500);
    }
}
