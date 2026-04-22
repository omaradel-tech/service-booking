<?php

namespace App\Modules\Booking\Models;

use App\Core\Domain\Enums\BookingStatus;
use Database\Factories\BookingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): BookingFactory
    {
        return BookingFactory::new();
    }

    protected $fillable = [
        'user_id',
        'service_id',
        'scheduled_at',
        'status',
    ];

    protected $casts = [
        'status' => BookingStatus::class,
        'scheduled_at' => 'datetime',
    ];

    /**
     * Get the user that owns the booking.
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /**
     * Get the service that owns the booking.
     */
    public function service()
    {
        return $this->belongsTo(\App\Modules\Service\Models\Service::class);
    }

    /**
     * Check if booking can be canceled.
     */
    public function canBeCanceled(): bool
    {
        return in_array($this->status, [
            BookingStatus::PENDING(),
            BookingStatus::CONFIRMED(),
        ]) && $this->scheduled_at->isFuture();
    }

    /**
     * Scope to only include bookings with specific status.
     */
    public function scopeWithStatus($query, BookingStatus $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to only include future bookings.
     */
    public function scopeFuture($query)
    {
        return $query->where('scheduled_at', '>', now());
    }
}
