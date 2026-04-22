<?php

namespace App\Core\Application\Contracts;

use App\Models\User;
use App\Modules\Booking\Models\Booking;
use Illuminate\Database\Eloquent\Collection;

interface BookingRepositoryInterface
{
    /**
     * Create a new booking.
     */
    public function create(array $data): Booking;

    /**
     * Get bookings for user.
     */
    public function getForUser(User $user): Collection;

    /**
     * Check if user has overlapping bookings.
     */
    public function hasOverlap(User $user, \DateTime $startTime, \DateTime $endTime, ?Booking $excludeBooking = null): bool;

    /**
     * Find booking by ID.
     */
    public function find(int $id): ?Booking;

    /**
     * Update booking.
     */
    public function update(Booking $booking, array $data): bool;

    /**
     * Delete booking.
     */
    public function delete(Booking $booking): bool;

    /**
     * Get bookings by status.
     */
    public function getByStatus(string $status): Collection;

    /**
     * Get future bookings for user.
     */
    public function getFutureForUser(User $user): Collection;
}
