<?php


declare(strict_types=1);

use PayPHP\Services\TaxDataService;
use PayPHP\Services\TaxCodeParsingService;

beforeEach(function () {
    $this->taxDataService = TaxDataService::fromJsonFile('data/TaxYears/2025-26.json');
    $this->taxCodeParser = new TaxCodeParsingService();
});

describe('Tax Data Service - Basic Functionality', function () {
    it('can load tax data from JSON file', function () {
        expect($this->taxDataService)->toBeInstanceOf(TaxDataService::class);
    });

    it('throws exception for non-existent file', function () {
        expect(fn() => TaxDataService::fromJsonFile('non-existent-file.json'))
            ->toThrow(Error::class);
    });
});

describe('UK Region Tax Data', function () {
    it('returns correct UK tax rates', function () {
        $rates = $this->taxDataService->getRatesForRegion('uk');

        expect($rates)->toHaveKey('basicRate')
            ->and($rates)->toHaveKey('higherRate')
            ->and($rates)->toHaveKey('additionalRate')
            ->and($rates['basicRate'])->toBe(0.20)
            ->and($rates['higherRate'])->toBe(0.40)
            ->and($rates['additionalRate'])->toBe(0.45);
    });

    it('returns correct UK tax bands', function () {
        $bands = $this->taxDataService->getBandsForRegion('uk');

        expect($bands)->toHaveKey('basicRateBand')
            ->and($bands)->toHaveKey('higherRateBand')
            ->and($bands)->toHaveKey('additionalRateBand')
            ->and($bands['basicRateBand']['start'])->toBe(241)
            ->and($bands['basicRateBand']['end'])->toBe(966)
            ->and($bands['higherRateBand']['start'])->toBe(967)
            ->and($bands['higherRateBand']['end'])->toBe(2406)
            ->and($bands['additionalRateBand']['start'])->toBe(2407)
            ->and($bands['additionalRateBand']['end'])->toBeNull();
    });

    it('gets complete tax data for UK tax code', function () {
        $taxCode = $this->taxCodeParser->parseTaxCode('1257L');
        $taxData = $this->taxDataService->getTaxDataForTaxCode($taxCode);

        expect($taxData)->toHaveKey('rates')
            ->and($taxData)->toHaveKey('bands')
            ->and($taxData['rates']['basicRate'])->toBe(0.20)
            ->and($taxData['bands']['basicRateBand']['start'])->toBe(241);
    });
});

describe('Wales Region Tax Data', function () {
    it('returns correct Welsh tax rates', function () {
        $rates = $this->taxDataService->getRatesForRegion('wales');

        expect($rates)->toHaveKey('basicRate')
            ->and($rates)->toHaveKey('higherRate')
            ->and($rates)->toHaveKey('additionalRate')
            ->and($rates['basicRate'])->toBe(0.20)
            ->and($rates['higherRate'])->toBe(0.40)
            ->and($rates['additionalRate'])->toBe(0.45);
    });

    it('returns correct Welsh tax bands', function () {
        $bands = $this->taxDataService->getBandsForRegion('wales');

        expect($bands)->toHaveKey('basicRateBand')
            ->and($bands)->toHaveKey('higherRateBand')
            ->and($bands)->toHaveKey('additionalRateBand')
            ->and($bands['basicRateBand']['start'])->toBe(241)
            ->and($bands['basicRateBand']['end'])->toBe(966);
    });

    it('gets complete tax data for Welsh tax code', function () {
        $taxCode = $this->taxCodeParser->parseTaxCode('C1257L');
        $taxData = $this->taxDataService->getTaxDataForTaxCode($taxCode);

        expect($taxCode->region)->toBe('wales')
            ->and($taxData['rates']['basicRate'])->toBe(0.20)
            ->and($taxData['bands']['basicRateBand']['start'])->toBe(241);
    });
});

describe('Scotland Region Tax Data', function () {
    it('returns correct Scottish tax rates', function () {
        $rates = $this->taxDataService->getRatesForRegion('scotland');

        expect($rates)->toHaveKey('starterRate')
            ->and($rates)->toHaveKey('basicRate')
            ->and($rates)->toHaveKey('intermediateRate')
            ->and($rates)->toHaveKey('higherRate')
            ->and($rates)->toHaveKey('advancedRate')
            ->and($rates)->toHaveKey('topRate')
            ->and($rates['starterRate'])->toBe(0.19)
            ->and($rates['basicRate'])->toBe(0.20)
            ->and($rates['intermediateRate'])->toBe(0.21)
            ->and($rates['higherRate'])->toBe(0.42)
            ->and($rates['advancedRate'])->toBe(0.45)
            ->and($rates['topRate'])->toBe(0.48);
    });

    it('returns correct Scottish tax bands', function () {
        $bands = $this->taxDataService->getBandsForRegion('scotland');

        expect($bands)->toHaveKey('starterRateBand')
            ->and($bands)->toHaveKey('basicRateBand')
            ->and($bands)->toHaveKey('intermediateRateBand')
            ->and($bands)->toHaveKey('higherRateBand')
            ->and($bands)->toHaveKey('advancedRateBand')
            ->and($bands)->toHaveKey('topRateBand')
            ->and($bands['starterRateBand']['start'])->toBe(241)
            ->and($bands['starterRateBand']['end'])->toBe(296)
            ->and($bands['basicRateBand']['start'])->toBe(297)
            ->and($bands['basicRateBand']['end'])->toBe(528)
            ->and($bands['intermediateRateBand']['start'])->toBe(529)
            ->and($bands['intermediateRateBand']['end'])->toBe(840)
            ->and($bands['higherRateBand']['start'])->toBe(841)
            ->and($bands['higherRateBand']['end'])->toBe(1442)
            ->and($bands['advancedRateBand']['start'])->toBe(1443)
            ->and($bands['advancedRateBand']['end'])->toBe(2406)
            ->and($bands['topRateBand']['start'])->toBe(2407)
            ->and($bands['topRateBand']['end'])->toBeNull();
    });

    it('gets complete tax data for Scottish tax code', function () {
        $taxCode = $this->taxCodeParser->parseTaxCode('S1257L');
        $taxData = $this->taxDataService->getTaxDataForTaxCode($taxCode);

        expect($taxCode->region)->toBe('scotland')
            ->and($taxData['rates']['starterRate'])->toBe(0.19)
            ->and($taxData['rates']['basicRate'])->toBe(0.20)
            ->and($taxData['bands']['starterRateBand']['start'])->toBe(241)
            ->and($taxData['bands']['basicRateBand']['start'])->toBe(297);
    });
});

describe('Tax Data Service with Different Tax Codes', function () {
    it('correctly handles UK emergency tax codes', function () {
        $taxCode = $this->taxCodeParser->parseTaxCode('1257LX');
        $taxData = $this->taxDataService->getTaxDataForTaxCode($taxCode);

        expect($taxCode->region)->toBe('uk')
            ->and($taxCode->isEmergency)->toBeTrue()
            ->and($taxData['rates']['basicRate'])->toBe(0.20);
    });

    it('correctly handles Welsh K codes', function () {
        $taxCode = $this->taxCodeParser->parseTaxCode('CK456');
        $taxData = $this->taxDataService->getTaxDataForTaxCode($taxCode);

        expect($taxCode->region)->toBe('wales')
            ->and($taxCode->isAllowanceNegative)->toBeTrue()
            ->and($taxData['rates']['basicRate'])->toBe(0.20);
    });

    it('correctly handles Scottish K codes', function () {
        $taxCode = $this->taxCodeParser->parseTaxCode('SK789L');
        $taxData = $this->taxDataService->getTaxDataForTaxCode($taxCode);

        expect($taxCode->region)->toBe('scotland')
            ->and($taxCode->isAllowanceNegative)->toBeTrue()
            ->and($taxData['rates']['starterRate'])->toBe(0.19)
            ->and($taxData['rates']['basicRate'])->toBe(0.20);
    });

    it('correctly handles complex Scottish codes', function () {
        $taxCode = $this->taxCodeParser->parseTaxCode('SK1257LX');
        $taxData = $this->taxDataService->getTaxDataForTaxCode($taxCode);

        expect($taxCode->region)->toBe('scotland')
            ->and($taxCode->isAllowanceNegative)->toBeTrue()
            ->and($taxCode->isEmergency)->toBeTrue()
            ->and($taxData['rates']['intermediateRate'])->toBe(0.21);
    });
});

describe('Error Handling', function () {
    it('throws exception for invalid region', function () {
        expect(fn() => $this->taxDataService->getRatesForRegion('invalid'))
            ->toThrow(InvalidArgumentException::class, 'Unknown region: invalid');
    });

    it('throws exception for invalid region in bands', function () {
        expect(fn() => $this->taxDataService->getBandsForRegion('nonexistent'))
            ->toThrow(InvalidArgumentException::class, 'Unknown region: nonexistent');
    });

    it('handles invalid tax code gracefully', function () {
        // The tax code parser will throw an exception for invalid codes
        expect(fn() => $this->taxCodeParser->parseTaxCode('INVALID123'))
            ->toThrow(InvalidArgumentException::class);
    });
});

describe('Data Structure Validation', function () {
    it('ensures rates data does not contain band information', function () {
        $ukRates = $this->taxDataService->getRatesForRegion('uk');
        $scottishRates = $this->taxDataService->getRatesForRegion('scotland');

        // Check no keys end with 'Band'
        foreach (array_keys($ukRates) as $key) {
            expect(str_ends_with($key, 'Band'))->toBeFalse();
        }

        foreach (array_keys($scottishRates) as $key) {
            expect(str_ends_with($key, 'Band'))->toBeFalse();
        }
    });

    it('ensures bands data only contains band information', function () {
        $ukBands = $this->taxDataService->getBandsForRegion('uk');
        $scottishBands = $this->taxDataService->getBandsForRegion('scotland');

        // Check all keys end with 'Band'
        foreach (array_keys($ukBands) as $key) {
            expect(str_ends_with($key, 'Band'))->toBeTrue();
        }

        foreach (array_keys($scottishBands) as $key) {
            expect(str_ends_with($key, 'Band'))->toBeTrue();
        }
    });

    it('validates band structure has start and end values', function () {
        $bands = $this->taxDataService->getBandsForRegion('uk');

        foreach ($bands as $bandName => $band) {
            expect($band)->toHaveKey('start')
                ->and($band)->toHaveKey('end')
                ->and($band['start'])->toBeInt();

            // 'end' can be null for top bands
            if ($band['end'] !== null) {
                expect($band['end'])->toBeInt();
            }
        }
    });
});

describe('Integration Tests', function () {
    it('works end-to-end for all regions', function () {
        $testCodes = [
            'uk' => '1257L',
            'wales' => 'C1257L',
            'scotland' => 'S1257L'
        ];

        foreach ($testCodes as $expectedRegion => $codeString) {
            $taxCode = $this->taxCodeParser->parseTaxCode($codeString);
            $taxData = $this->taxDataService->getTaxDataForTaxCode($taxCode);

            expect($taxCode->region)->toBe($expectedRegion)
                ->and($taxData)->toHaveKey('rates')
                ->and($taxData)->toHaveKey('bands')
                ->and($taxData['rates'])->toBeArray()
                ->and($taxData['bands'])->toBeArray();
        }
    });

    it('handles all supported tax code combinations', function () {
        $testCombinations = [
            'CBR',      // Wales Basic Rate
            'SK123',    // Scotland K-code
            'CK456W1',  // Wales K-code with Week 1
            'S0T',      // Scotland 0T
            'CNT',      // Wales No Tax
            'SD0',      // Scotland D0
        ];

        foreach ($testCombinations as $codeString) {
            $taxCode = $this->taxCodeParser->parseTaxCode($codeString);
            $taxData = $this->taxDataService->getTaxDataForTaxCode($taxCode);

            expect($taxData)->toHaveKey('rates')
                ->and($taxData)->toHaveKey('bands')
                ->and($taxCode->region)->toBeIn(['uk', 'wales', 'scotland']);
        }
    });
});