<?php

declare(strict_types=1);

namespace PayPHP\Actions;

use PayPHP\Dto\TaxCode;

final class CalculateTaxableIncomeAction
{
    public static function calculate(string $periodNumber, TaxCode $taxCode, float $payForPeriod) {}
}
