<?php

declare(strict_types=1);

use PayPHP\Services\TaxCodeParsingService;

beforeEach(function () {
    $this->service = new TaxCodeParsingService();
});

describe('Regional Prefix Handling', function () {
    it('correctly parses Scottish tax codes', function () {
        $scottishCode = $this->service->parseTaxCode('S1257L');

        expect($scottishCode->region)->toBe('scotland')
            ->and($scottishCode->code)->toBe('S1257L')
            ->and($scottishCode->suffix)->toBe('L')
            ->and($scottishCode->numericPart)->toBe(1257)
            ->and($scottishCode->isAllowanceNegative)->toBeFalse()
            ->and($scottishCode->isCumulative)->toBeTrue();
    });

    it('correctly parses Welsh tax codes', function () {
        $welshCode = $this->service->parseTaxCode('C1257M');

        expect($welshCode->region)->toBe('wales')
            ->and($welshCode->code)->toBe('C1257M')
            ->and($welshCode->suffix)->toBe('M')
            ->and($welshCode->numericPart)->toBe(1257)
            ->and($welshCode->isAllowanceNegative)->toBeFalse();
    });

    it('correctly parses UK tax codes with no prefix', function () {
        $ukCode = $this->service->parseTaxCode('1257L');

        expect($ukCode->region)->toBe('uk')
            ->and($ukCode->code)->toBe('1257L')
            ->and($ukCode->suffix)->toBe('L')
            ->and($ukCode->numericPart)->toBe(1257);
    });

    it('correctly parses Welsh special codes', function () {
        $welshBR = $this->service->parseTaxCode('CBR');

        expect($welshBR->region)->toBe('wales')
            ->and($welshBR->code)->toBe('CBR')
            ->and($welshBR->suffix)->toBe('BR')
            ->and($welshBR->numericPart)->toBe(0)
            ->and($welshBR->allowance)->toBe(0.0);
    });

    it('correctly parses Scottish special codes', function () {
        $scottishNT = $this->service->parseTaxCode('SNT');

        expect($scottishNT->region)->toBe('scotland')
            ->and($scottishNT->code)->toBe('SNT')
            ->and($scottishNT->suffix)->toBe('NT')
            ->and($scottishNT->numericPart)->toBe(0)
            ->and($scottishNT->allowance)->toBe(999999.99);
    });

    it('works with all special codes and regional prefixes', function () {
        $specialCodes = ['BR', 'D0', 'D1', 'D2', 'NT', '0T'];

        foreach ($specialCodes as $code) {
            // Test with Scotland prefix
            $scottishSpecial = $this->service->parseTaxCode('S'.$code);
            expect($scottishSpecial->region)->toBe('scotland')
                ->and($scottishSpecial->suffix)->toBe($code);

            // Test with Wales prefix
            $welshSpecial = $this->service->parseTaxCode('C'.$code);
            expect($welshSpecial->region)->toBe('wales')
                ->and($welshSpecial->suffix)->toBe($code);
        }
    });
});

describe('K-Code (Negative Allowance) Handling', function () {
    it('correctly parses basic K codes', function () {
        $kCode = $this->service->parseTaxCode('K123');

        expect($kCode->region)->toBe('uk')
            ->and($kCode->code)->toBe('K123')
            ->and($kCode->isAllowanceNegative)->toBeTrue()
            ->and($kCode->numericPart)->toBe(123)
            ->and($kCode->suffix)->toBe('K');
    });

    it('correctly parses Scottish K codes', function () {
        $scottishK = $this->service->parseTaxCode('SK456');

        expect($scottishK->region)->toBe('scotland')
            ->and($scottishK->code)->toBe('SK456')
            ->and($scottishK->isAllowanceNegative)->toBeTrue()
            ->and($scottishK->numericPart)->toBe(456)
            ->and($scottishK->suffix)->toBe('K');
    });

    it('correctly parses Welsh K codes', function () {
        $welshK = $this->service->parseTaxCode('CK789');

        expect($welshK->region)->toBe('wales')
            ->and($welshK->code)->toBe('CK789')
            ->and($welshK->isAllowanceNegative)->toBeTrue()
            ->and($welshK->numericPart)->toBe(789)
            ->and($welshK->suffix)->toBe('K');
    });

    it('correctly parses K codes with standard suffixes', function () {
        $kCodeL = $this->service->parseTaxCode('K234L');

        expect($kCodeL->region)->toBe('uk')
            ->and($kCodeL->code)->toBe('K234L')
            ->and($kCodeL->isAllowanceNegative)->toBeTrue()
            ->and($kCodeL->numericPart)->toBe(234)
            ->and($kCodeL->suffix)->toBe('L');
    });

    it('correctly handles Welsh K codes with emergency indicators', function () {
        $welshKEmergency = $this->service->parseTaxCode('CK123X');

        expect($welshKEmergency->region)->toBe('wales')
            ->and($welshKEmergency->code)->toBe('CK123X')
            ->and($welshKEmergency->isAllowanceNegative)->toBeTrue()
            ->and($welshKEmergency->isEmergency)->toBeTrue()
            ->and($welshKEmergency->isCumulative)->toBeFalse();
    });

    it('maintains allowance as positive value with negative flag', function () {
        $kCode = $this->service->parseTaxCode('K500');

        expect($kCode->isAllowanceNegative)->toBeTrue()
            ->and($kCode->allowance)->toBeGreaterThan(0);
        // The allowance should be positive - the negative handling is done elsewhere
    });
});

describe('Emergency and Non-Cumulative Code Recognition', function () {
    it('correctly identifies X suffix emergency codes', function () {
        $emergencyX = $this->service->parseTaxCode('1257LX');

        expect($emergencyX->code)->toBe('1257LX')
            ->and($emergencyX->isEmergency)->toBeTrue()
            ->and($emergencyX->isCumulative)->toBeFalse()
            ->and($emergencyX->suffix)->toBe('L')
            ->and($emergencyX->numericPart)->toBe(1257);
    });

    it('correctly identifies EMERGENCY suffix codes', function () {
        $emergencyFull = $this->service->parseTaxCode('1257LEMERGENCY');

        expect($emergencyFull->code)->toBe('1257LEMERGENCY')
            ->and($emergencyFull->isEmergency)->toBeTrue()
            ->and($emergencyFull->isCumulative)->toBeFalse();
    });

    it('correctly identifies W1 (Week 1) indicator', function () {
        $week1 = $this->service->parseTaxCode('1257LW1');

        expect($week1->code)->toBe('1257LW1')
            ->and($week1->isEmergency)->toBeFalse()
            ->and($week1->isCumulative)->toBeFalse()
            ->and($week1->suffix)->toBe('L')
            ->and($week1->numericPart)->toBe(1257);
    });

    it('correctly identifies M1 (Month 1) indicator', function () {
        $month1 = $this->service->parseTaxCode('1257MM1');

        expect($month1->code)->toBe('1257MM1')
            ->and($month1->isEmergency)->toBeFalse()
            ->and($month1->isCumulative)->toBeFalse()
            ->and($month1->suffix)->toBe('M')
            ->and($month1->numericPart)->toBe(1257);
    });

    it('correctly handles Scottish emergency codes', function () {
        $scottishEmergency = $this->service->parseTaxCode('S1257LX');

        expect($scottishEmergency->region)->toBe('scotland')
            ->and($scottishEmergency->isEmergency)->toBeTrue()
            ->and($scottishEmergency->isCumulative)->toBeFalse();
    });

    it('correctly handles Welsh K codes with W1', function () {
        $welshKW1 = $this->service->parseTaxCode('CK456W1');

        expect($welshKW1->region)->toBe('wales')
            ->and($welshKW1->isAllowanceNegative)->toBeTrue()
            ->and($welshKW1->isCumulative)->toBeFalse()
            ->and($welshKW1->isEmergency)->toBeFalse();
    });

    it('correctly handles complex combinations', function () {
        $complexCode = $this->service->parseTaxCode('SK789LX');

        expect($complexCode->region)->toBe('scotland')
            ->and($complexCode->isAllowanceNegative)->toBeTrue()
            ->and($complexCode->isEmergency)->toBeTrue()
            ->and($complexCode->isCumulative)->toBeFalse()
            ->and($complexCode->suffix)->toBe('L')
            ->and($complexCode->numericPart)->toBe(789);
    });

    it('correctly identifies normal cumulative codes', function () {
        $normalCode = $this->service->parseTaxCode('1257L');

        expect($normalCode->isEmergency)->toBeFalse()
            ->and($normalCode->isCumulative)->toBeTrue();
    });
});

describe('Edge Cases and Error Handling', function () {
    it('throws exception for empty tax code', function () {
        expect(fn () => $this->service->parseTaxCode(''))
            ->toThrow(InvalidArgumentException::class, 'Tax code cannot be empty');
    });

    it('throws exception for invalid format', function () {
        expect(fn () => $this->service->parseTaxCode('INVALID123'))
            ->toThrow(InvalidArgumentException::class, 'Invalid tax code format');
    });

    it('handles case insensitive input correctly', function () {
        $lowerCode = $this->service->parseTaxCode('s1257l');

        expect($lowerCode->region)->toBe('scotland')
            ->and($lowerCode->code)->toBe('S1257L')
            ->and($lowerCode->suffix)->toBe('L')
            ->and($lowerCode->numericPart)->toBe(1257);
        // Should be normalized to uppercase
    });

    it('handles mixed case input correctly', function () {
        $mixedCode = $this->service->parseTaxCode('Ck456m');

        expect($mixedCode->region)->toBe('wales')
            ->and($mixedCode->isAllowanceNegative)->toBeTrue()
            ->and($mixedCode->code)->toBe('CK456M');
    });

    it('handles whitespace correctly', function () {
        $paddedCode = $this->service->parseTaxCode('  S1257L  ');

        expect($paddedCode->region)->toBe('scotland')
            ->and($paddedCode->code)->toBe('S1257L')
            ->and($paddedCode->suffix)->toBe('L');
    });
});

describe('Integration with CalculateWeeklyTaxAllowance', function () {
    it('calls CalculateWeeklyTaxAllowance for standard codes', function () {
        $code = $this->service->parseTaxCode('1257L');

        // The allowance should be set by CalculateWeeklyTaxAllowance::calculate()
        expect($code->allowance)->toBeFloat()
            ->and($code->allowance)->toBeGreaterThan(0);
    });

    it('calls CalculateWeeklyTaxAllowance for K codes but keeps positive value', function () {
        $kCode = $this->service->parseTaxCode('K500');

        // The allowance should be positive (negative handling done elsewhere)
        expect($kCode->allowance)->toBeFloat()
            ->and($kCode->allowance)->toBeGreaterThan(0)
            ->and($kCode->isAllowanceNegative)->toBeTrue();
    });

    it('handles special codes with fixed allowances', function () {
        $brCode = $this->service->parseTaxCode('BR');
        expect($brCode->allowance)->toBe(0.0);

        $ntCode = $this->service->parseTaxCode('NT');
        expect($ntCode->allowance)->toBe(999999.99);
    });
});
