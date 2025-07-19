<?php

declare(strict_types=1);

namespace PayPHP\Services;

use InvalidArgumentException;
use Money\Currency;
use Money\Money;
use PayPHP\Dto\TaxCode;

final class TaxCodeParsingService
{
    public function parseTaxCode(string $taxCodeString): TaxCode
    {
        $taxCodeString = mb_strtoupper(mb_trim($taxCodeString));

        if (empty($taxCodeString)) {
            throw new InvalidArgumentException('Tax code cannot be empty');
        }

        $taxCode = new TaxCode();
        $taxCode->code = $taxCodeString;
        $taxCode->isEmergency = $this->isEmergencyCode($taxCodeString);
        $taxCode->isCumulative = ! $this->isNonCumulativeCode($taxCodeString);

        // Determine region from prefix
        if (str_starts_with($taxCodeString, 'S')) {
            $taxCode->region = 'scotland';
            $taxCodeString = mb_substr($taxCodeString, 1);
        } elseif (str_starts_with($taxCodeString, 'C')) {
            $taxCode->region = 'wales';
            $taxCodeString = mb_substr($taxCodeString, 1);
        } else {
            $taxCode->region = 'uk';
        }

        // Extract suffix and numeric part
        $this->parseCodeComponents($taxCodeString, $taxCode);

        return $taxCode;
    }

    private function parseCodeComponents(string $taxCode, TaxCode $taxCodeObj): void
    {
        $cleanCode = $this->removeEmergencyIndicators($taxCode);

        if ($this->isSpecialCode($cleanCode)) {
            $this->handleSpecialCode($cleanCode, $taxCodeObj);

            return;
        }

        if ($this->isKCode($cleanCode)) {
            $this->handleKCode($cleanCode, $taxCodeObj);

            return;
        }

        if ($this->isStandardCode($cleanCode)) {
            $this->handleStandardCode($cleanCode, $taxCodeObj);

            return;
        }

        throw new InvalidArgumentException("Invalid tax code format: {$taxCode}");
    }

    private function removeEmergencyIndicators(string $taxCode): string
    {
        return str_replace(['X', 'W1', 'M1', 'EMERGENCY'], '', $taxCode);
    }

    private function isSpecialCode(string $code): bool
    {
        return isset($this->getSpecialCodes()[$code]);
    }

    private function isKCode(string $code): bool
    {
        return (bool) preg_match('/^K(\d+)$/', $code);
    }

    private function isStandardCode(string $code): bool
    {
        return (bool) preg_match('/^(\d+)([LMNT])$/', $code);
    }

    private function handleSpecialCode(string $code, TaxCode $taxCodeObj): void
    {
        $codeData = $this->getSpecialCodes()[$code];
        $taxCodeObj->suffix = $codeData['suffix'];
        $taxCodeObj->numericPart = $codeData['numericPart'];
        $taxCodeObj->allowance = new Money((int) ($codeData['allowance'] * 100), new Currency('GBP'));
    }

    private function handleKCode(string $code, TaxCode $taxCodeObj): void
    {
        preg_match('/^K(\d+)$/', $code, $matches);
        $taxCodeObj->suffix = 'K';
        $taxCodeObj->numericPart = (int) $matches[1];
        $taxCodeObj->isAllowanceNegative = true;
        $taxCodeObj->allowance = new Money((int) ((int) $matches[1] * 10 * 100), new Currency('GBP'));
    }

    private function handleStandardCode(string $code, TaxCode $taxCodeObj): void
    {
        preg_match('/^(\d+)([LMNT])$/', $code, $matches);

        $numericPart = (int) $matches[1];
        $suffix = $matches[2];

        $baseAllowance = $numericPart * 10;

        // Apply Marriage Allowance adjustments
        if ($suffix === 'M') {
            // +10%
            $adjustedAllowance = round($baseAllowance * 1.10, 2);
        } elseif ($suffix === 'N') {
            // -10%
            $adjustedAllowance = round($baseAllowance * 0.90, 2);
        } else {
            $adjustedAllowance = $baseAllowance;
        }

        $taxCodeObj->numericPart = $numericPart;
        $taxCodeObj->suffix = $suffix;
        $taxCodeObj->allowance = new Money((int) ($adjustedAllowance * 100), new Currency('GBP'));
    }

    private function getSpecialCodes(): array
    {
        return [
            'BR' => ['suffix' => 'BR', 'numericPart' => 0, 'allowance' => 0],
            'D0' => ['suffix' => 'D0', 'numericPart' => 0, 'allowance' => 0],
            'D1' => ['suffix' => 'D1', 'numericPart' => 0, 'allowance' => 0],
            'D2' => ['suffix' => 'D1', 'numericPart' => 0, 'allowance' => 0],
            'NT' => ['suffix' => 'NT', 'numericPart' => 0, 'allowance' => PHP_INT_MAX / 100],
            '0T' => ['suffix' => '0T', 'numericPart' => 0, 'allowance' => 0],
        ];
    }

    private function isEmergencyCode(string $taxCode): bool
    {
        return str_contains($taxCode, 'X') ||
            str_contains($taxCode, 'EMERGENCY') ||
            preg_match('/\d+[LMN]X$/', $taxCode);
    }

    private function isNonCumulativeCode(string $taxCode): bool
    {
        return str_contains($taxCode, 'W1') ||
            str_contains($taxCode, 'M1') ||
            $this->isEmergencyCode($taxCode);
    }
}
