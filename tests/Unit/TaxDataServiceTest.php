<?php

declare(strict_types=1);

use PayPHP\Services\TaxCodeParsingService;
use PayPHP\Services\TaxDataService;

beforeEach(function () {
    $this->taxDataService = TaxDataService::fromJsonFile('data/TaxYears/2025-26.json');
    $this->taxCodeParser = new TaxCodeParsingService();
});

describe('Tax Data Service - Basic Functionality', function () {
    it('can load tax data from JSON file', function () {
        expect($this->taxDataService)->toBeInstanceOf(TaxDataService::class);
    });

    it('throws exception for non-existent file', function () {
        expect(fn () => TaxDataService::fromJsonFile('non-existent-file.json'))
            ->toThrow(Error::class);
    });
});

describe('UK Region Tax Data', function () {
    it('returns correct UK tax rates', function () {
        $rates = $this->taxDataService->getRatesForRegion('uk');

        expect($rates)->toHaveKey('basic')
            ->and($rates)->toHaveKey('higher')
            ->and($rates)->toHaveKey('additional')
            ->and($rates['basic'])->toBe(0.20)
            ->and($rates['higher'])->toBe(0.40)
            ->and($rates['additional'])->toBe(0.45);
    });

    it('returns correct UK tax bands', function () {
        $bands = $this->taxDataService->getBandsForRegion('uk');

        expect($bands)->toHaveKey('basic')
            ->and($bands)->toHaveKey('higher')
            ->and($bands)->toHaveKey('additional')
            ->and($bands['basic']['start'])->toBe(241)
            ->and($bands['basic']['end'])->toBe(966)
            ->and($bands['higher']['start'])->toBe(967)
            ->and($bands['higher']['end'])->toBe(2406)
            ->and($bands['additional']['start'])->toBe(2407)
            ->and($bands['additional']['end'])->toBeNull();
    });

    it('gets complete tax data for UK tax code', function () {
        $taxCode = $this->taxCodeParser->parseTaxCode('1257L');
        $taxData = $this->taxDataService->getTaxDataForTaxCode($taxCode);

        expect($taxData)->toHaveKey('rates')
            ->and($taxData)->toHaveKey('bands')
            ->and($taxData['rates']['basic'])->toBe(0.20)
            ->and($taxData['bands']['basic']['start'])->toBe(241);
    });
});

describe('Wales Region Tax Data', function () {
    it('returns correct Welsh tax rates', function () {
        $rates = $this->taxDataService->getRatesForRegion('wales');

        expect($rates)->toHaveKey('basic')
            ->and($rates)->toHaveKey('higher')
            ->and($rates)->toHaveKey('additional')
            ->and($rates['basic'])->toBe(0.20)
            ->and($rates['higher'])->toBe(0.40)
            ->and($rates['additional'])->toBe(0.45);
    });

    it('returns correct Welsh tax bands', function () {
        $bands = $this->taxDataService->getBandsForRegion('wales');

        expect($bands)->toHaveKey('basic')
            ->and($bands)->toHaveKey('higher')
            ->and($bands)->toHaveKey('additional')
            ->and($bands['basic']['start'])->toBe(241)
            ->and($bands['basic']['end'])->toBe(966);
    });

    it('gets complete tax data for Welsh tax code', function () {
        $taxCode = $this->taxCodeParser->parseTaxCode('C1257L');
        $taxData = $this->taxDataService->getTaxDataForTaxCode($taxCode);

        expect($taxCode->region)->toBe('wales')
            ->and($taxData['rates']['basic'])->toBe(0.20)
            ->and($taxData['bands']['basic']['start'])->toBe(241);
    });
});

describe('Scotland Region Tax Data', function () {
    it('returns correct Scottish tax rates', function () {
        $rates = $this->taxDataService->getRatesForRegion('scotland');

        expect($rates)->toHaveKey('starter')
            ->and($rates)->toHaveKey('basic')
            ->and($rates)->toHaveKey('intermediate')
            ->and($rates)->toHaveKey('higher')
            ->and($rates)->toHaveKey('advanced')
            ->and($rates)->toHaveKey('top')
            ->and($rates['starter'])->toBe(0.19)
            ->and($rates['basic'])->toBe(0.20)
            ->and($rates['intermediate'])->toBe(0.21)
            ->and($rates['higher'])->toBe(0.42)
            ->and($rates['advanced'])->toBe(0.45)
            ->and($rates['top'])->toBe(0.48);
    });

    it('returns correct Scottish tax bands', function () {
        $bands = $this->taxDataService->getBandsForRegion('scotland');

        expect($bands)->toHaveKey('starter')
            ->and($bands)->toHaveKey('basic')
            ->and($bands)->toHaveKey('intermediate')
            ->and($bands)->toHaveKey('higher')
            ->and($bands)->toHaveKey('advanced')
            ->and($bands)->toHaveKey('top')
            ->and($bands['starter']['start'])->toBe(241)
            ->and($bands['starter']['end'])->toBe(296)
            ->and($bands['basic']['start'])->toBe(297)
            ->and($bands['basic']['end'])->toBe(528)
            ->and($bands['intermediate']['start'])->toBe(529)
            ->and($bands['intermediate']['end'])->toBe(840)
            ->and($bands['higher']['start'])->toBe(841)
            ->and($bands['higher']['end'])->toBe(1442)
            ->and($bands['advanced']['start'])->toBe(1443)
            ->and($bands['advanced']['end'])->toBe(2406)
            ->and($bands['top']['start'])->toBe(2407)
            ->and($bands['top']['end'])->toBeNull();
    });

    it('gets complete tax data for Scottish tax code', function () {
        $taxCode = $this->taxCodeParser->parseTaxCode('S1257L');
        $taxData = $this->taxDataService->getTaxDataForTaxCode($taxCode);

        expect($taxCode->region)->toBe('scotland')
            ->and($taxData['rates']['starter'])->toBe(0.19)
            ->and($taxData['rates']['basic'])->toBe(0.20)
            ->and($taxData['bands']['starter']['start'])->toBe(241)
            ->and($taxData['bands']['basic']['start'])->toBe(297);
    });
});

describe('Tax Data Service with Different Tax Codes', function () {
    it('correctly handles UK emergency tax codes', function () {
        $taxCode = $this->taxCodeParser->parseTaxCode('1257LX');
        $taxData = $this->taxDataService->getTaxDataForTaxCode($taxCode);

        expect($taxCode->region)->toBe('uk')
            ->and($taxCode->isEmergency)->toBeTrue()
            ->and($taxData['rates']['basic'])->toBe(0.20);
    });

    it('correctly handles Welsh K codes', function () {
        $taxCode = $this->taxCodeParser->parseTaxCode('CK456');
        $taxData = $this->taxDataService->getTaxDataForTaxCode($taxCode);

        expect($taxCode->region)->toBe('wales')
            ->and($taxCode->isAllowanceNegative)->toBeTrue()
            ->and($taxData['rates']['basic'])->toBe(0.20);
    });

    it('correctly handles Scottish K codes', function () {
        $taxCode = $this->taxCodeParser->parseTaxCode('SK789L');
        $taxData = $this->taxDataService->getTaxDataForTaxCode($taxCode);

        expect($taxCode->region)->toBe('scotland')
            ->and($taxCode->isAllowanceNegative)->toBeTrue()
            ->and($taxData['rates']['starter'])->toBe(0.19)
            ->and($taxData['rates']['basic'])->toBe(0.20);
    });

    it('correctly handles complex Scottish codes', function () {
        $taxCode = $this->taxCodeParser->parseTaxCode('SK1257LX');
        $taxData = $this->taxDataService->getTaxDataForTaxCode($taxCode);

        expect($taxCode->region)->toBe('scotland')
            ->and($taxCode->isAllowanceNegative)->toBeTrue()
            ->and($taxCode->isEmergency)->toBeTrue()
            ->and($taxData['rates']['intermediate'])->toBe(0.21);
    });
});

describe('Error Handling', function () {
    it('throws exception for invalid region', function () {
        expect(fn () => $this->taxDataService->getRatesForRegion('invalid'))
            ->toThrow(InvalidArgumentException::class, 'Unknown region: invalid');
    });

    it('throws exception for invalid region in bands', function () {
        expect(fn () => $this->taxDataService->getBandsForRegion('nonexistent'))
            ->toThrow(InvalidArgumentException::class, 'Unknown region: nonexistent');
    });

    it('handles invalid tax code gracefully', function () {
        // The tax code parser will throw an exception for invalid codes
        expect(fn () => $this->taxCodeParser->parseTaxCode('INVALID123'))
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

        // Check bands appear in bands
        foreach ($ukBands as $band) {
            expect($band)->toHaveKey('start')
                ->and($band)->toHaveKey('end');
        }

        foreach ($scottishBands as $band) {
            expect($band)->toHaveKey('start')
                ->and($band)->toHaveKey('end');
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
            'scotland' => 'S1257L',
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
