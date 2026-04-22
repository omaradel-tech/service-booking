<?php

namespace App\Modules\Booking\Observers;

use App\Modules\Booking\Models\Booking;
use App\Modules\Booking\Jobs\SendBookingConfirmation;
use App\Core\Domain\Enums\BookingStatus;
use Illuminate\Support\Facades\Log;

class BookingObserver
{
    /**
     * Handle the Booking "created" event.
     */
    public function created(Booking $booking): void
    {
        // Send confirmation email
        SendBookingConfirmation::dispatch($booking);

        Log::info('Booking created and confirmation dispatched', [
            'booking_id' => $booking->id,
            'user_id' => $booking->user_id,
            'service_id' => $booking->service_id,
        ]);
    }

    /**
     * Handle the Booking "updated" event.
     */
    public function updated(Booking $booking): void
    {
        if ($booking->wasChanged('status')) {
            Log::info('Booking status changed', [
                'booking_id' => $booking->id,
                'old_status' => $booking->getOriginal('status'),
                'new_status' => $booking->status,
            ]);

            // Send notification for status changes if needed
            if ($booking->status === BookingStatus::CONFIRMED) {
                // Could dispatch a job to send confirmation
            } elseif ($booking->status === BookingStatus::CANCELED) {
                // Could dispatch a job to send cancellation notice
            }
        }
    }

    /**
     * Handle the Booking "deleted" event.
     */
    public function deleted(Booking $booking): void
    {
        Log::info('Booking deleted', [
            'booking_id' => $booking->id,
            'user_id' => $booking->user_id,
        ]);
    }
}
