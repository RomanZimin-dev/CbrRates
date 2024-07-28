<?php

declare(strict_types=1);

use CbrCurrencyRates\CbrCurrencyRates;
use CbrCurrencyRates\Communication\Exception\BadCurrencyException;
use CbrCurrencyRates\Communication\Exception\BadResponseException;
use CbrCurrencyRates\Communication\Exception\CommunicationException;
use CbrCurrencyRates\Exception\BadInputException;
use GuzzleHttp\Exception\GuzzleException;
use DI\ContainerBuilder;

require_once __DIR__ . '/vendor/autoload.php';

/*
$inputDataValidator = new InputDataValidator();

$requestBuilder = new RequestBuilder();

$responseDataValidator = new ResponseDataValidator();
$responseBuilder = new ResponseBuilder($responseDataValidator);

$client = new Client();
$communicationHandler = new CommunicationHandler($requestBuilder, $responseBuilder, $client);
$cbrCurrencyRates = new CbrCurrencyRates($inputDataValidator, $communicationHandler);
*/
$date = $argv[1];
$currencyCode = $argv[2];
$baseCurrencyCode = ($argv[3])?? null;

//\CbrCurrencyRates\Cache\Cache::flush();

try {
    // PHP-DI чтобы не собирать композицию вручную
    $containerBuilder = new ContainerBuilder();
    $container = $containerBuilder->build();
    $cbrCurrencyRates = $container->get(CbrCurrencyRates::class);

    $dataToShow = $cbrCurrencyRates->getCrossRateData($date, $currencyCode, $baseCurrencyCode);

    echo $dataToShow['rate'] . " (" . ($dataToShow['differenceFromPreviousMarketDate'] > 0? "+" : "") . $dataToShow['differenceFromPreviousMarketDate'] . ")\n";
} catch (BadCurrencyException|BadResponseException $e) {
    echo "[Request-Response Error] " . $e->getMessage();
} catch (BadInputException $e) {
    echo "[Bad Input Format] " . $e->getMessage();
} catch (CommunicationException $e) {
    echo "[Connection Error] " . $e->getMessage();
} catch (GuzzleException|Exception $e) {
    echo "[Something went wrong] " . $e->getMessage();
}



