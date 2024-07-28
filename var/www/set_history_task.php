<?php

declare(strict_types=1);

use CbrCurrencyRates\CbrCurrencyRates;
use CbrCurrencyRates\Exception\BadInputException;
use CbrCurrencyRates\RMQ\RMQ;
use CbrCurrencyRates\Validator\InputDataValidator;

require_once __DIR__ . '/vendor/autoload.php';

$historyDaysCnt = 180;

if (isset($argv[1])) {
    $historyDaysCnt = (int) $argv[1];
}

$currencyCode = $argv[2] ?? "";
$baseCurrencyCode = $argv[3] ?? CbrCurrencyRates::DEFAULT_BASE_CURRENCY_CODE;

try {
    $inputDataValidator = new InputDataValidator();
    $inputDataValidator->validateCurrencyCode($currencyCode);
    $inputDataValidator->validateCurrencyCode($baseCurrencyCode);
} catch (BadInputException $e) {
    echo "[Bad Input Format] " . $e->getMessage() . "\n";
    exit;
}

echo "Adding " . $historyDaysCnt . " tasks to get cbr rates...\n";

$todayDate = new DateTimeImmutable();

// i = 1, потому что собрать нужно за Х предыдущих (!) дней
for ($i = 1; $i <= $historyDaysCnt; $i++) {
    $date = $todayDate->sub(new DateInterval('P1D'));
    $formattedDate = $date->format("d/m/Y");
    RMQ::addTask([
        "date" => $formattedDate,
        "currencyCode" => $currencyCode,
        "baseCurrencyCode" => $baseCurrencyCode,
    ]);

    echo "Task for getting cbr rate " . $currencyCode . $baseCurrencyCode . " added for date: " . $formattedDate . "\n";
    $todayDate = $date;
}
RMQ::close();
