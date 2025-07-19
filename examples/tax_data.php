<?php

declare(strict_types=1);
require_once __DIR__.'/../vendor/autoload.php';

use PayPHP\Services\TaxCodeParsingService;
use PayPHP\Services\TaxDataService;

$taxCodeParsingService = new TaxCodeParsingService();
$data = TaxDataService::fromJsonFile(__DIR__.'/../data/TaxYears/2025-26.json');
$code = $taxCodeParsingService->parseTaxCode('1257L');
dd($data->getTaxDataForTaxCode($code));
