<?php

declare(strict_types=1);

namespace PayPHP\Dto;

use Money\Currency;
use Money\Money;

final class TaxCode
{
    public string $code;

    public Money $allowance;

    public bool $isAllowanceNegative = false;

    public bool $isCumulative;

    public bool $isEmergency;

    public string $suffix;

    public int $numericPart;

    public string $region = 'uk'; // Default to UK

    public function __construct()
    {
        $this->allowance = new Money(0, new Currency('GBP'));
    }

    public function shouldUseFlatRate(): bool
    {
        return in_array($this->suffix, ['BR', 'D0', 'D1', 'D2']);
    }
}
