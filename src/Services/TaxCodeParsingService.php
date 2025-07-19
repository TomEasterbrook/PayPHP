<?php

declare(strict_types=1);

namespace PayPHP\Services;

use InvalidArgumentException;
use PayPHP\Actions\CalculateWeeklyTaxAllowance;
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

        // Parse all prefixes (regional and K)
        $parsedPrefixes = $this->parsePrefixes($taxCodeString);
        $taxCode->region = $parsedPrefixes['region'];
        $taxCode->isAllowanceNegative = $parsedPrefixes['isNegative'];
        $remainingCode = $parsedPrefixes['remainingCode'];

        // Extract suffix and numeric part from the remaining code
        $this->parseCodeComponents($remainingCode, $taxCode);

        return $taxCode;
    }

    private function parsePrefixes(string $taxCode): array
    {
        $region = 'uk';
        $isNegative = false;
        $remainingCode = $taxCode;

        // Check for regional prefix first (S or C)
        if (str_starts_with($taxCode, 'S')) {
            $region = 'scotland';
            $remainingCode = mb_substr($remainingCode, 1);
        } elseif (str_starts_with($taxCode, 'C')) {
            $region = 'wales';
            $remainingCode = mb_substr($remainingCode, 1);
        }

        // Check for K prefix (negative allowance) after regional prefix
        if (str_starts_with($remainingCode, 'K')) {
            $isNegative = true;
            $remainingCode = mb_substr($remainingCode, 1);
        }

        return [
            'region' => $region,
            'isNegative' => $isNegative,
            'remainingCode' => $remainingCode,
        ];
    }

    private function parseCodeComponents(string $taxCode, TaxCode $taxCodeObj): void
    {
        $cleanCode = $this->removeEmergencyIndicators($taxCode);

        if ($this->isSpecialCode($cleanCode)) {
            $this->handleSpecialCode($cleanCode, $taxCodeObj);

            return;
        }

        if ($this->isStandardCode($cleanCode)) {
            $this->handleStandardCode($cleanCode, $taxCodeObj);

            return;
        }

        // Handle numeric-only codes (like after K prefix removal)
        if ($this->isNumericOnlyCode($cleanCode)) {
            $this->handleNumericOnlyCode($cleanCode, $taxCodeObj);

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

    private function isStandardCode(string $code): bool
    {
        return (bool) preg_match('/^(\d+)([LMNT])$/', $code);
    }

    private function isNumericOnlyCode(string $code): bool
    {
        return (bool) preg_match('/^(\d+)$/', $code);
    }

    private function handleSpecialCode(string $code, TaxCode $taxCodeObj): void
    {
        $codeData = $this->getSpecialCodes()[$code];
        $taxCodeObj->suffix = $codeData['suffix'];
        $taxCodeObj->numericPart = $codeData['numericPart'];
        $taxCodeObj->allowance = $codeData['allowance'];
    }

    private function handleStandardCode(string $code, TaxCode $taxCodeObj): void
    {
        preg_match('/^(\d+)([LMNT])$/', $code, $matches);

        $numericPart = (int) $matches[1];
        $suffix = $matches[2];

        $this->setAllowanceFromNumericPart($numericPart, $suffix, $taxCodeObj);

        $taxCodeObj->numericPart = $numericPart;
        $taxCodeObj->suffix = $suffix;
    }

    private function handleNumericOnlyCode(string $code, TaxCode $taxCodeObj): void
    {
        $numericPart = (int) $code;

        // For K codes, we typically don't have a suffix, or it's implied as 'L'
        $suffix = $taxCodeObj->isAllowanceNegative ? 'K' : 'L';

        $this->setAllowanceFromNumericPart($numericPart, $suffix, $taxCodeObj);

        $taxCodeObj->numericPart = $numericPart;
        $taxCodeObj->suffix = $suffix;
    }

    private function setAllowanceFromNumericPart(int $numericPart, string $suffix, TaxCode $taxCodeObj): void
    {
        $taxCodeObj->allowance = CalculateWeeklyTaxAllowance::calculate($numericPart);
    }

    private function getSpecialCodes(): array
    {
        return [
            'BR' => ['suffix' => 'BR', 'numericPart' => 0, 'allowance' => 0.0],
            'D0' => ['suffix' => 'D0', 'numericPart' => 0, 'allowance' => 0.0],
            'D1' => ['suffix' => 'D1', 'numericPart' => 0, 'allowance' => 0.0],
            'D2' => ['suffix' => 'D2', 'numericPart' => 0, 'allowance' => 0.0],
            'NT' => ['suffix' => 'NT', 'numericPart' => 0, 'allowance' => 999999.99], // Large float for no tax
            '0T' => ['suffix' => '0T', 'numericPart' => 0, 'allowance' => 0.0],
        ];
    }

    private function isEmergencyCode(string $taxCode): bool
    {
        return str_contains($taxCode, 'X') ||
            str_contains($taxCode, 'EMERGENCY') ||
            preg_match('/\d+[LMNT]X$/', $taxCode);
    }

    private function isNonCumulativeCode(string $taxCode): bool
    {
        return str_contains($taxCode, 'W1') ||
            str_contains($taxCode, 'M1') ||
            $this->isEmergencyCode($taxCode);
    }
}
