<?php

namespace App\Modules\Package\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PackageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => $this->price,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'services_total_price' => $this->when(isset($this->services_total_price), $this->services_total_price),
            'discount_amount' => $this->when(isset($this->discount_amount), $this->discount_amount),
            'discount_percentage' => $this->when(isset($this->discount_percentage), $this->discount_percentage),
            'services' => $this->whenLoaded('services', function () {
                return $this->services->map(function ($service) {
                    return [
                        'id' => $service->id,
                        'name' => $service->name,
                        'price' => $service->price,
                        'description' => $service->description,
                        'duration_minutes' => $service->duration_minutes,
                    ];
                });
            }),
        ];
    }
}
