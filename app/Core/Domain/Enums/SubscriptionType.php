<?php

namespace App\Core\Domain\Enums;

use omaradel\Enum\Supports\Enum;

/**
 * @method static static TRIAL()
 * @method static static PAID()
 */
class SubscriptionType extends Enum
{
    const TRIAL = 'trial';
    const PAID = 'paid';
}
