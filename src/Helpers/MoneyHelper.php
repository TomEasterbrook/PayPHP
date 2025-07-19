<?php

declare(strict_types=1);

namespace PayPHP\Helpers;

use Money\Money;

final class MoneyHelper
{
    public static function floorToNearestPound(Money $money): Money
    {
        // Money is in minor units (pence), so divide by 100 to get whole pounds
        $pounds = intdiv((int) $money->getAmount(), 100);

        // Multiply back to get Money in pence again
        return Money::GBP($pounds * 100);
    }
}
