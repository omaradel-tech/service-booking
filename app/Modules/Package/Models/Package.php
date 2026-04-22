<?php

namespace App\Modules\Package\Models;

use Database\Factories\PackageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return PackageFactory::new();
    }

    protected $fillable = [
        'name',
        'price',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * The services that belong to the package.
     */
    public function services()
    {
        return $this->belongsToMany(\App\Modules\Service\Models\Service::class, 'package_services');
    }

    /**
     * Get the total price of all services in the package.
     */
    public function getServicesTotalPriceAttribute(): float
    {
        return $this->services->sum('price');
    }

    /**
     * Get the discount amount (services total - package price).
     */
    public function getDiscountAmountAttribute(): float
    {
        return $this->services_total_price - $this->price;
    }

    /**
     * Get the discount percentage.
     */
    public function getDiscountPercentageAttribute(): float
    {
        if ($this->services_total_price == 0) {
            return 0;
        }

        return ($this->discount_amount / $this->services_total_price) * 100;
    }

    /**
     * Scope to only include active packages.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Check if package price is valid (less than sum of service prices).
     */
    public function isValidPrice(): bool
    {
        return $this->price < $this->services_total_price;
    }
}
