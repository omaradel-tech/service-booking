<?php

namespace App\Core\Infrastructure\Repositories;

use App\Core\Application\Contracts\BookingRepositoryInterface;
use App\Models\User;
use App\Modules\Booking\Models\Booking;
use Illuminate\Database\Eloquent\Collection;

class EloquentBookingRepository implements BookingRepositoryInterface
{
    /**
     * Create a new booking.
     */
    public function create(array $data): Booking
    {
        return Booking::create($data);
    }

    /**
     * Get bookings for user.
     */
    public function getForUser(User $user): Collection
    {
        return Booking::where('user_id', $user->id)
            ->with('service')
            ->orderBy('scheduled_at', 'desc')
            ->get();
    }

    /**
     * Check if user has overlapping bookings.
     */
    public function hasOverlap(User $user, \DateTime $startTime, \DateTime $endTime, ?Booking $excludeBooking = null): bool
    {
        // Get all potential overlapping bookings
        $query = Booking::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->where(function ($q) use ($startTime, $endTime) {
                $q->whereBetween('scheduled_at', [$startTime, $endTime])
                  ->orWhere('scheduled_at', '<=', $startTime);
            })
            ->with('service');

        if ($excludeBooking) {
            $query->where('id', '!=', $excludeBooking->id);
        }

        $bookings = $query->get();

        // Check each booking for overlap in PHP
        foreach ($bookings as $booking) {
            $bookingEndTime = (new \DateTime($booking->scheduled_at))
                ->add(new \DateInterval('PT' . $booking->service->duration_minutes . 'M'));

            // Check if booking overlaps with the requested time slot
            if ($bookingEndTime > $startTime && $booking->scheduled_at < $endTime) {
                return true;
            }
        }

        return false;
    }

    /**
     * Find booking by ID.
     */
    public function find(int $id): ?Booking
    {
        return Booking::with(['user', 'service'])->find($id);
    }

    /**
     * Update booking.
     */
    public function update(Booking $booking, array $data): bool
    {
        return $booking->update($data);
    }

    /**
     * Delete booking.
     */
    public function delete(Booking $booking): bool
    {
        return $booking->delete();
    }

    /**
     * Get bookings by status.
     */
    public function getByStatus(string $status): Collection
    {
        return Booking::where('status', $status)
            ->with(['user', 'service'])
            ->orderBy('scheduled_at')
            ->get();
    }

    /**
     * Get future bookings for user.
     */
    public function getFutureForUser(User $user): Collection
    {
        return Booking::where('user_id', $user->id)
            ->where('scheduled_at', '>', now())
            ->with('service')
            ->orderBy('scheduled_at')
            ->get();
    }
}
