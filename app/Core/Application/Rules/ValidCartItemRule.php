<?php

namespace App\Core\Application\Rules;

use App\Core\Application\Contracts\ServiceRepositoryInterface;
use App\Core\Application\Services\PackageService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidCartItemRule implements ValidationRule
{
    public function __construct(
        private ServiceRepositoryInterface $serviceRepository,
        private PackageService $packageService
    ) {}

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $itemType = request()->input('item_type');
        $itemId = request()->input('item_id');

        if (!$itemType || !$itemId) {
            $fail('Invalid cart item data provided.');
            return;
        }

        if ($itemType === 'service') {
            $service = $this->serviceRepository->find($itemId);
            if (!$service || !$service->is_active) {
                $fail('The selected service is not available.');
            }
        } elseif ($itemType === 'package') {
            $package = $this->packageService->find($itemId);
            if (!$package || !$package->is_active) {
                $fail('The selected package is not available.');
            }
        } else {
            $fail('Invalid item type specified.');
        }
    }
}
