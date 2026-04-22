<?php

namespace App\Core\Domain\Enums;

use omaradel\Enum\Supports\Enum;

/**
 * @method static static PENDING()
 * @method static static CONFIRMED()
 * @method static static COMPLETED()
 * @method static static CANCELED()
 */
class BookingStatus extends Enum
{
    const PENDING = 'pending';
    const CONFIRMED = 'confirmed';
    const COMPLETED = 'completed';
    const CANCELED = 'canceled';
}
