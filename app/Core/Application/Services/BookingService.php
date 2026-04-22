<?php

namespace App\Core\Application\Services;

use App\Core\Application\Contracts\BookingRepositoryInterface;
use App\Core\Application\Contracts\SubscriptionRepositoryInterface;
use App\Core\Application\DTOs\CreateBookingDTO;
use App\Core\Domain\Enums\BookingStatus;
use App\Models\User;
use App\Modules\Booking\Models\Booking;
use App\Modules\Booking\Jobs\SendBookingConfirmation;
use App\Modules\Service\Models\Service;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BookingService
{
    public function __construct(
        private BookingRepositoryInterface $bookingRepository,
        private SubscriptionRepositoryInterface $subscriptionRepository
    ) {}

    /**
     * Create a new booking with validation.
     */
    public function create(CreateBookingDTO $dto): Booking
    {
        return DB::transaction(function () use ($dto) {
            // Validate user has active subscription
            if (!$this->subscriptionRepository->getActiveForUser($dto->user)) {
                throw new \Exception('Active subscription required to create booking');
            }

            // Validate scheduled time is in the future
            if ($dto->scheduledAt->isPast()) {
                throw new \Exception('Booking time must be in the future');
            }

            // Lock service row for update to prevent concurrent bookings
            $service = Service::whereKey($dto->serviceId)->lockForUpdate()->firstOrFail();
            $endTime = $dto->scheduledAt->copy()->addMinutes($service->duration_minutes);
            
            // Re-check for overlapping bookings within transaction
            if ($this->bookingRepository->hasOverlap($dto->user, $dto->scheduledAt, $endTime)) {
                throw new \Exception('Booking time conflicts with existing booking');
            }

            $bookingData = [
                'user_id' => $dto->user->id,
                'service_id' => $dto->serviceId,
                'scheduled_at' => $dto->scheduledAt,
                'status' => BookingStatus::PENDING(),
            ];

            $booking = $this->bookingRepository->create($bookingData);

            // Dispatch confirmation job
            SendBookingConfirmation::dispatch($booking);

            Log::info('Booking created', [
                'user_id' => $dto->user->id,
                'booking_id' => $booking->id,
                'service_id' => $dto->serviceId,
                'scheduled_at' => $dto->scheduledAt,
            ]);

            return $booking;
        });
    }

    /**
     * Confirm a booking.
     */
    public function confirm(Booking $booking): bool
    {
        if ($booking->status !== BookingStatus::PENDING()) {
            throw new \Exception('Only pending bookings can be confirmed');
        }

        $updated = $this->bookingRepository->update($booking, [
            'status' => BookingStatus::CONFIRMED(),
        ]);

        if ($updated) {
            Log::info('Booking confirmed', [
                'booking_id' => $booking->id,
            ]);
        }

        return $updated;
    }

    /**
     * Cancel a booking.
     */
    public function cancel(Booking $booking): bool
    {
        if (!$booking->canBeCanceled()) {
            throw new \Exception('Booking cannot be canceled');
        }

        $updated = $this->bookingRepository->update($booking, [
            'status' => BookingStatus::CANCELED(),
        ]);

        if ($updated) {
            Log::info('Booking canceled', [
                'booking_id' => $booking->id,
            ]);
        }

        return $updated;
    }

    /**
     * Complete a booking.
     */
    public function complete(Booking $booking): bool
    {
        if ($booking->status !== BookingStatus::CONFIRMED()) {
            throw new \Exception('Only confirmed bookings can be completed');
        }

        if ($booking->scheduled_at->isFuture()) {
            throw new \Exception('Cannot complete future bookings');
        }

        $updated = $this->bookingRepository->update($booking, [
            'status' => BookingStatus::COMPLETED(),
        ]);

        if ($updated) {
            Log::info('Booking completed', [
                'booking_id' => $booking->id,
            ]);
        }

        return $updated;
    }

    /**
     * Get bookings for user.
     */
    public function getForUser(User $user): \Illuminate\Database\Eloquent\Collection
    {
        return $this->bookingRepository->getForUser($user);
    }

    /**
     * Get future bookings for user.
     */
    public function getFutureForUser(User $user): \Illuminate\Database\Eloquent\Collection
    {
        return $this->bookingRepository->getFutureForUser($user);
    }
}
