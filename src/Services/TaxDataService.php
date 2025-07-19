<?php
declare(strict_types=1);

namespace PayPHP\Services;

use InvalidArgumentException;
use PayPHP\Dto\TaxCode;

final class TaxDataService
{
    private array $taxData;

    public function __construct(array $taxData)
    {
        $this->taxData = $taxData;
    }

    public function getRatesForRegion(string $region): array
    {
        if (!isset($this->taxData['regions'][$region])) {
            throw new InvalidArgumentException("Unknown region: {$region}");
        }

        $data = $this->taxData['regions'][$region];

        // Remove bands from the data, keep only rates
        return array_filter($data, function($key) {
            return !str_ends_with($key, 'Band');
        }, ARRAY_FILTER_USE_KEY);
    }

    public function getBandsForRegion(string $region): array
    {
        if (!isset($this->taxData['regions'][$region])) {
            throw new InvalidArgumentException("Unknown region: {$region}");
        }

        $data = $this->taxData['regions'][$region];

        // Get only the band data
        return array_filter($data, function($key) {
            return str_ends_with($key, 'Band');
        }, ARRAY_FILTER_USE_KEY);
    }

    public function getTaxDataForTaxCode(TaxCode $taxCode): array
    {
        return [
            'rates' => $this->getRatesForRegion($taxCode->region),
            'bands' => $this->getBandsForRegion($taxCode->region)
        ];
    }

    public static function fromJsonFile(string $filePath): self
    {
        $data = json_decode(file_get_contents($filePath), true);
        return new self($data);
    }
}