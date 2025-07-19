<?php

declare(strict_types=1);

use PayPHP\Actions\CalculateWeeklyTaxAllowance;

test('Code 0 returns £0.00', function () {
    $weeklyTaxFree = CalculateWeeklyTaxAllowance::calculate(0);
    expect($weeklyTaxFree)->toBe(0.00);
});

test('Code 1 returns £0.37', function () {
    $weeklyTaxFree = CalculateWeeklyTaxAllowance::calculate(1);
    expect($weeklyTaxFree)->toBe(0.37); // (0*10)+9 = 9 → /52 = 0.173 → ceil = 0.18 ← wrong!
    // Correct logic: adjustedCode = 0, q = 0, r = 1 → (1*10)+9 = 19 → 19/52 = 0.36538 → round up = 0.37
});

test('Code 500 returns £96.33', function () {
    $weeklyTaxFree = CalculateWeeklyTaxAllowance::calculate(500);
    expect($weeklyTaxFree)->toBe(96.33); // adjustedCode = 499 → q=0, r=500 → (500×10+9)/52 = 5009/52 = 96.327 → round up = 96.33
});

test('Code 501 returns £192.49', function () {
    $weeklyTaxFree = CalculateWeeklyTaxAllowance::calculate(501);
    expect($weeklyTaxFree)->toBe(96.53);
});

test('Code 1000 returns £192.49', function () {
    $weeklyTaxFree = CalculateWeeklyTaxAllowance::calculate(1000);
    // adjusted = 999 → q=1, r=500 → 5009/52 = 96.33 → + 96.16 = 192.49
    expect($weeklyTaxFree)->toBe(192.49);
});

test('Code 1250 returns £240.57', function () {
    $weeklyTaxFree = CalculateWeeklyTaxAllowance::calculate(1250);
    expect($weeklyTaxFree)->toBe(240.57);
});

test('Code 1256 returns £241.73', function () {
    $weeklyTaxFree = CalculateWeeklyTaxAllowance::calculate(1256);
    expect($weeklyTaxFree)->toBe(241.73);
});

test('Code 1257 returns £241.92', function () {
    $weeklyTaxFree = CalculateWeeklyTaxAllowance::calculate(1257);
    // adjusted = 1256 → q=2, r=257 → (257×10)+9 = 2579/52 = 49.596 → round up = 49.60
    // total = 192.32 + 49.60 = 241.92
    expect($weeklyTaxFree)->toBe(241.92);
});
