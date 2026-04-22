<?php

namespace App\Modules\Subscription\Models;

use App\Core\Domain\Enums\SubscriptionStatus;
use App\Core\Domain\Enums\SubscriptionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

class Subscription extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'type',
        'status',
        'starts_at',
        'ends_at',
        'grace_ends_at',
    ];

    protected $casts = [
        'type' => SubscriptionType::class,
        'status' => SubscriptionStatus::class,
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'grace_ends_at' => 'datetime',
    ];

    /**
     * Get the user that owns the subscription.
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /**
     * Check if subscription is currently active.
     */
    public function isActive(): bool
    {
        $statusCheck = $this->status == SubscriptionStatus::ACTIVE();
        $futureCheck = $this->ends_at->isFuture();
        
        Log::info('Subscription isActive check', [
            'subscription_id' => $this->id,
            'status' => $this->status->getValue(),
            'status_check' => $statusCheck,
            'ends_at' => $this->ends_at->toIso8601String(),
            'now' => now()->toIso8601String(),
            'future_check' => $futureCheck,
            'is_future_method' => $this->ends_at->isFuture(),
            'ends_at_timestamp' => $this->ends_at->timestamp,
            'now_timestamp' => now()->timestamp
        ]);
        
        return $statusCheck && $futureCheck;
    }

    /**
     * Check if subscription is in grace period.
     */
    public function isInGracePeriod(): bool
    {
        return $this->grace_ends_at 
            && $this->grace_ends_at->isFuture() 
            && $this->ends_at->isPast();
    }
}
