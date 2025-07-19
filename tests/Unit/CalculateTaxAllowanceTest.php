<?php

declare(strict_types=1);

use PayPHP\Actions\CalculateTaxAllowance;

// Weekly Tests
test('Code 0 returns £0.00 (weekly)', function () {
    $weeklyTaxFree = CalculateTaxAllowance::calculate(0, 'weekly');
    expect($weeklyTaxFree)->toBe(0.00);
});

test('Code 1 returns £0.37 (weekly)', function () {
    $weeklyTaxFree = CalculateTaxAllowance::calculate(1, 'weekly');
    expect($weeklyTaxFree)->toBe(0.37);
});

test('Code 500 returns £96.33 (weekly)', function () {
    $weeklyTaxFree = CalculateTaxAllowance::calculate(500, 'weekly');
    expect($weeklyTaxFree)->toBe(96.33);
});

test('Code 501 returns £96.53 (weekly)', function () {
    $weeklyTaxFree = CalculateTaxAllowance::calculate(501, 'weekly');
    expect($weeklyTaxFree)->toBe(96.53);
});

test('Code 1000 returns £192.49 (weekly)', function () {
    $weeklyTaxFree = CalculateTaxAllowance::calculate(1000, 'weekly');
    expect($weeklyTaxFree)->toBe(192.49);
});

test('Code 1250 returns £240.57 (weekly)', function () {
    $weeklyTaxFree = CalculateTaxAllowance::calculate(1250, 'weekly');
    expect($weeklyTaxFree)->toBe(240.57);
});

test('Code 1256 returns £241.73 (weekly)', function () {
    $weeklyTaxFree = CalculateTaxAllowance::calculate(1256, 'weekly');
    expect($weeklyTaxFree)->toBe(241.73);
});

test('Code 1257 returns £241.92 (weekly)', function () {
    $weeklyTaxFree = CalculateTaxAllowance::calculate(1257, 'weekly');
    expect($weeklyTaxFree)->toBe(241.92);
});

// Monthly Tests
test('Code 0 returns £0.00 (monthly)', function () {
    $monthlyTaxFree = CalculateTaxAllowance::calculate(0, 'monthly');
    expect($monthlyTaxFree)->toBe(0.00);
});

test('Code 1 returns £1.61 (monthly)', function () {
    $monthlyTaxFree = CalculateTaxAllowance::calculate(1, 'monthly');
    expect($monthlyTaxFree)->toBe(1.61);
});

test('Code 500 returns £417.43 (monthly)', function () {
    $monthlyTaxFree = CalculateTaxAllowance::calculate(500, 'monthly');
    expect($monthlyTaxFree)->toBe(417.43);
});

test('Code 501 returns £418.30 (monthly)', function () {
    $monthlyTaxFree = CalculateTaxAllowance::calculate(501, 'monthly');
    expect($monthlyTaxFree)->toBe(418.30);
});

test('Code 1000 returns £834.13 (monthly)', function () {
    $monthlyTaxFree = CalculateTaxAllowance::calculate(1000, 'monthly');
    expect($monthlyTaxFree)->toBe(834.13);
});

test('Code 1250 returns £1042.47 (monthly)', function () {
    $monthlyTaxFree = CalculateTaxAllowance::calculate(1250, 'monthly');
    expect($monthlyTaxFree)->toBe(1042.47);
});

test('Code 1256 returns £1047.50 (monthly)', function () {
    $monthlyTaxFree = CalculateTaxAllowance::calculate(1256, 'monthly');
    expect($monthlyTaxFree)->toBe(1047.50);
});

test('Code 1257 returns £1048.32 (monthly)', function () {
    $monthlyTaxFree = CalculateTaxAllowance::calculate(1257, 'monthly');
    expect($monthlyTaxFree)->toBe(1048.32);
});
