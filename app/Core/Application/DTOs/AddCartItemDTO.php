<?php

namespace App\Core\Application\DTOs;

use App\Models\User;

class AddCartItemDTO
{
    public function __construct(
        public readonly User $user,
        public readonly string $itemType,
        public readonly int $itemId,
        public readonly int $quantity = 1
    ) {}
}
