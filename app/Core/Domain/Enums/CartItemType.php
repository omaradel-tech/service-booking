<?php

namespace App\Core\Domain\Enums;

use omaradel\Enum\Supports\Enum;

/**
 * @method static static SERVICE()
 * @method static static PACKAGE()
 */
class CartItemType extends Enum
{
    const SERVICE = 'service';
    const PACKAGE = 'package';
}
