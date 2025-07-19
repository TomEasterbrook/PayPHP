<?php

declare(strict_types=1);

namespace PayPHP\Actions;

final class CalculateTaxAllowance
{
    public static function calculate(int $taxCodeNumericPart, string $period = 'weekly'): float
    {
        if ($taxCodeNumericPart === 0) {
            return 0.00;
        }

        $adjustedCode = $taxCodeNumericPart - 1;

        $quotient = intdiv($adjustedCode, 500);
        $remainder = ($adjustedCode % 500) + 1;

        $weeklyQuotientValue = $quotient * 96.16;

        $annualRemainderValue = ($remainder * 10) + 9;
        $weeklyRemainderValue = self::ceilToNearestPenny($annualRemainderValue / 52);
        $totalWeeklyFreePay = $weeklyRemainderValue + $weeklyQuotientValue;

        return $period === 'weekly'
            ? $totalWeeklyFreePay
            : ceil($totalWeeklyFreePay * (52 / 12) * 100) / 100;
    }

    private static function ceilToNearestPenny($value): float|int
    {
        // Round up to next penny if not already at exact penny
        return ceil($value * 100) / 100;
    }
}
