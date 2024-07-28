<?php

declare(strict_types=1);

namespace CbrCurrencyRates\Communication\Validator;

use CbrCurrencyRates\CbrCurrencyRates;
use CbrCurrencyRates\Communication\CbrResponse;
use CbrCurrencyRates\Communication\Exception\BadCurrencyException;
use CbrCurrencyRates\Communication\Exception\BadResponseException;
use CbrCurrencyRates\Communication\Exception\CommunicationException;
use DateTimeImmutable;
use Psr\Http\Message\ResponseInterface;

class ResponseDataValidator
{
    private const SUCCESS_STATUS_CODE = 200;

    /**
     * @param ResponseInterface $response
     * @throws BadResponseException
     * @throws CommunicationException
     */
    public function validate(ResponseInterface $response)
    {
        if ($response->getStatusCode() !== self::SUCCESS_STATUS_CODE) {
            throw new CommunicationException("Cbr is not answered.");
        }

        $stream = $response->getBody();
        $responseBody = $stream->getContents();
        $xmlObject = simplexml_load_string($responseBody);

        if ($xmlObject === false) {
            throw new BadResponseException("XML is not valid.");
        }
        $valuteCount = count($xmlObject->Valute);

        if ($valuteCount === 0) {
            throw new BadResponseException("No rates found in Cbr response.");
        }
        $rateDate = DateTimeImmutable::createFromFormat("d.m.Y", (string) $xmlObject['Date']);

        if(!$rateDate || $rateDate->format('d.m.Y') !== (string) $xmlObject['Date']) {
            throw new BadResponseException("Bad date in Cbr response.");
        }
        $valutes = $xmlObject->Valute;

        foreach ($valutes as $valute) {
            // Проверим что CharCode не пустой
            if ((string) $valute->CharCode === '') {
                throw new BadResponseException("Bad CharCode in Cbr response. Can't continue....");
            }

            // Проверим что курс - валидный float
            if (!is_numeric(str_replace(',', '.', (string) $valute->VunitRate))) {
                throw new BadResponseException("Bad Rate in Cbr response. Can't continue....");
            }
        }

        $stream->rewind();
    }

    /**
     * @param CbrResponse $cbrResponse
     * @param string $currencyCode
     * @param string $baseCurrencyCode
     * @throws BadCurrencyException
     */
    public function validateCurrencyExists(CbrResponse $cbrResponse, string $currencyCode, string $baseCurrencyCode)
    {
        // Выдёргиваем из него кросс-курс, если валюты не равнаself::DEFAULT_BASE_CURRENCY_CODE
        if (!isset($cbrResponse->getData()[$currencyCode])) {
            throw new BadCurrencyException("Currency with code " . $currencyCode . " is not exist");
        }

        if (
            $baseCurrencyCode !== CbrCurrencyRates::DEFAULT_BASE_CURRENCY_CODE
            && !isset($cbrResponse->getData()[$baseCurrencyCode])
            ) {
            throw new BadCurrencyException("Base currency with code " . $baseCurrencyCode . " is not exist");
        }
    }
}