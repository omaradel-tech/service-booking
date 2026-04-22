<?php

namespace App\Modules\Cart\Services;

use App\Models\User;
use App\Modules\Cart\DTOs\CheckoutDTO;
use App\Modules\Cart\Models\Cart;
use App\Modules\Cart\Models\CartItem;
use App\Modules\Booking\Models\Booking;
use App\Modules\Service\Models\Service;
use App\Modules\Package\Models\Package;
use App\Core\Domain\Enums\BookingStatus;
use App\Core\Domain\Enums\CartItemType;
use App\Exceptions\Domain\BookingConflictException;
use App\Exceptions\Domain\CartItemInvalidException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckoutService
{
    /**
     * Process checkout - convert cart items to bookings.
     */
    public function checkout(User $user, CheckoutDTO $dto): array
    {
        return DB::transaction(function () use ($user, $dto) {
            // Load cart with items and lock for update
            $cart = Cart::where('user_id', $user->id)
                ->with(['items' => fn($query) => $query->lockForUpdate()])
                ->lockForUpdate()
                ->firstOrFail();

            if ($cart->items->isEmpty()) {
                throw new CartItemInvalidException('Cart is empty');
            }

            $bookings = [];
            $processedCartItemIds = [];

            foreach ($dto->schedules as $schedule) {
                // Validate cart item belongs to user's cart
                $cartItem = $cart->items->firstWhere('id', $schedule->cart_item_id);
                if (!$cartItem) {
                    throw new CartItemInvalidException("Cart item {$schedule->cart_item_id} not found in user's cart");
                }

                // Validate service belongs to cart item
                if (!$this->validateServiceBelongsToCartItem($cartItem, $schedule->service_id)) {
                    throw new CartItemInvalidException("Service {$schedule->service_id} does not belong to cart item {$schedule->cart_item_id}");
                }

                // Check for booking conflicts (double-check within transaction)
                $this->validateNoBookingConflicts($user, $schedule->service_id, $schedule->scheduled_at);

                // Create booking
                $booking = Booking::create([
                    'user_id' => $user->id,
                    'service_id' => $schedule->service_id,
                    'scheduled_at' => $schedule->scheduled_at,
                    'status' => BookingStatus::PENDING(),
                ]);

                $bookings[] = $booking;
                $processedCartItemIds[] = $cartItem->id;

                Log::channel('booking')->info('Booking created from checkout', [
                    'booking_id' => $booking->id,
                    'user_id' => $user->id,
                    'service_id' => $schedule->service_id,
                    'scheduled_at' => $schedule->scheduled_at,
                    'cart_item_id' => $cartItem->id,
                ]);
            }

            // Clear processed cart items
            CartItem::whereIn('id', $processedCartItemIds)->delete();

            Log::channel('booking')->info('Checkout completed', [
                'user_id' => $user->id,
                'bookings_count' => count($bookings),
                'cart_items_cleared' => count($processedCartItemIds),
            ]);

            return $bookings;
        });
    }

    /**
     * Validate that the service belongs to the cart item (service or package).
     */
    private function validateServiceBelongsToCartItem(CartItem $cartItem, int $serviceId): bool
    {
        if ($cartItem->item_type === CartItemType::SERVICE) {
            return $cartItem->item_id === $serviceId;
        }

        if ($cartItem->item_type === CartItemType::PACKAGE) {
            $package = Package::find($cartItem->item_id);
            return $package && $package->services()->where('services.id', $serviceId)->exists();
        }

        return false;
    }

    /**
     * Validate no booking conflicts for the given service and time.
     */
    private function validateNoBookingConflicts(User $user, int $serviceId, $scheduledAt): void
    {
        $service = Service::lockForUpdate()->findOrFail($serviceId);
        
        $conflictingBooking = Booking::where('user_id', $user->id)
            ->where('service_id', $serviceId)
            ->where('scheduled_at', $scheduledAt)
            ->whereIn('status', [BookingStatus::PENDING(), BookingStatus::CONFIRMED()])
            ->first();

        if ($conflictingBooking) {
            throw new BookingConflictException('Booking conflicts with existing schedule', [
                'conflicting_booking_id' => $conflictingBooking->id,
                'service_id' => $serviceId,
                'scheduled_at' => $scheduledAt,
            ]);
        }

        // Additional overlap check using raw SQL for time-based conflicts
        $overlapQuery = "
            SELECT COUNT(*) as count 
            FROM bookings 
            WHERE user_id = ? 
            AND service_id = ? 
            AND scheduled_at = ? 
            AND status IN (?, ?)
        ";

        $result = DB::select($overlapQuery, [
            $user->id,
            $serviceId,
            $scheduledAt,
            BookingStatus::PENDING(),
            BookingStatus::CONFIRMED(),
        ]);

        if ($result[0]->count > 0) {
            throw new BookingConflictException('Booking time conflict detected', [
                'service_id' => $serviceId,
                'scheduled_at' => $scheduledAt,
            ]);
        }
    }
}
