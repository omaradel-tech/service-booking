<?php

namespace App\Modules\Service\Models;

use Database\Factories\ServiceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): ServiceFactory
    {
        return ServiceFactory::new();
    }

    protected $fillable = [
        'name',
        'description',
        'price',
        'duration_minutes',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'duration_minutes' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get the bookings for the service.
     */
    public function bookings()
    {
        return $this->hasMany(\App\Modules\Booking\Models\Booking::class);
    }

    /**
     * The packages that belong to the service.
     */
    public function packages()
    {
        return $this->belongsToMany(\App\Modules\Package\Models\Package::class, 'package_services');
    }

    /**
     * Scope to only include active services.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
