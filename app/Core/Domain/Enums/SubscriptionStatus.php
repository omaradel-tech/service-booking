<?php

namespace App\Core\Domain\Enums;

use omaradel\Enum\Supports\Enum;

/**
 * @method static static ACTIVE()
 * @method static static EXPIRED()
 * @method static static CANCELED()
 */
class SubscriptionStatus extends Enum
{
    const ACTIVE = 'active';
    const EXPIRED = 'expired';
    const CANCELED = 'canceled';
}
