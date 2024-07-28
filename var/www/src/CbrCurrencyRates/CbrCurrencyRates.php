<?php

declare(strict_types=1);

namespace CbrCurrencyRates;

use CbrCurrencyRates\Cache\Cache;
use CbrCurrencyRates\Communication\CommunicationHandler;
use CbrCurrencyRates\Communication\Exception\BadCurrencyException;
use CbrCurrencyRates\Communication\Exception\BadResponseException;
use CbrCurrencyRates\Communication\Exception\CommunicationException;
use CbrCurrencyRates\Exception\BadInputException;
use CbrCurrencyRates\Validator\InputDataValidator;
use DateInterval;
use DateTimeImmutable;
use GuzzleHttp\Exception\GuzzleException;

class CbrCurrencyRates
{
    public const DEFAULT_BASE_CURRENCY_CODE = "RUR";
    public const DEFAULT_ROUND_PRECISION_FOR_CROSS_RATES = 4;

    private InputDataValidator $inputDataValidator;
    private CommunicationHandler $communicationHandler;

    /**
     * CbrCurrencyRates constructor.
     * @param CommunicationHandler $communicationHandler
     */
    public function __construct(InputDataValidator $inputDataValidator, CommunicationHandler $communicationHandler)
    {
        $this->inputDataValidator = $inputDataValidator;
        $this->communicationHandler = $communicationHandler;
    }

    /**
     * @param float $todayRate
     * @param float $previousMarketDateRate
     * @return float
     */
    private function getDifferenceFromPreviousMarketDate(float $todayRate, float $previousMarketDateRate): float
    {
        // Cbr отдаёт курс с точностью 4 знака после запятой
        $multiplier = pow(10, self::DEFAULT_ROUND_PRECISION_FOR_CROSS_RATES);

        $todayRateIntValue = (int) ($todayRate * $multiplier);
        $previousMarketDateRateIntValue = (int) ($previousMarketDateRate * $multiplier);

        return (float)(($todayRateIntValue - $previousMarketDateRateIntValue) / $multiplier);
    }

    /**
     * @param string $date
     * @param string $currencyCode
     * @param string $baseCurrencyCode
     * @return RateData
     * @throws BadCurrencyException
     * @throws BadResponseException
     * @throws CommunicationException
     * @throws GuzzleException
     */
    public function getRateDataByDate(string $date, string $currencyCode, string $baseCurrencyCode)
    {
        // Пытаемся достать значение курса из кэша
        $cacheKey = $this->getKey($date, $currencyCode, $baseCurrencyCode);
        $todayRateJson = Cache::get($cacheKey);

        if (!$todayRateJson) {
            // Получаем текущий рублёвый рейт
            $cbrResponse = $this->communicationHandler->sendRequest($date, $currencyCode, $baseCurrencyCode);

            if ($baseCurrencyCode !== self::DEFAULT_BASE_CURRENCY_CODE) {
                $todayRate = $cbrResponse->getData()[$currencyCode] / $cbrResponse->getData()[$baseCurrencyCode];
            } else {
                $todayRate = $cbrResponse->getData()[$currencyCode];
            }
            $previousMarketDate = $cbrResponse->getRateDate()
                ->sub(new DateInterval('P1D'))
                ->format("d/m/Y");

            $rateData = new RateData($todayRate, $previousMarketDate);
            Cache::set($cacheKey, $rateData->getAsJson());
        } else {
            $rateDataArray = json_decode($todayRateJson, true);
            $rateData = new RateData($rateDataArray['rate'], $rateDataArray['previousMarketDate']);
        }

        return $rateData;
    }

    /**
     * @param string $date
     * @param string $currencyCode
     * @param string|null $baseCurrencyCode
     * @return array
     * @throws BadCurrencyException
     * @throws BadInputException
     * @throws BadResponseException
     * @throws CommunicationException
     * @throws GuzzleException
     */
    public function getCrossRateData(string $date, string $currencyCode, ?string $baseCurrencyCode = null): array
    {
        // Валидируем входные параметры
        $this->inputDataValidator->validateDate($date);
        $this->inputDataValidator->validateCurrencyCode($currencyCode);

        if (is_null($baseCurrencyCode)) {
            $baseCurrencyCode = self::DEFAULT_BASE_CURRENCY_CODE;
        } else {
            $this->inputDataValidator->validateCurrencyCode($baseCurrencyCode);
        }
        $todayRateData = $this->getRateDataByDate($date, $currencyCode, $baseCurrencyCode);
        $previousMarketDate = $todayRateData->getPreviousMarketDate();
        $previousMarketDateRateData = $this->getRateDataByDate($previousMarketDate, $currencyCode, $baseCurrencyCode);

        return [
            'rate' => $todayRateData->getRate(),
            'differenceFromPreviousMarketDate' => $this->getDifferenceFromPreviousMarketDate(
                $todayRateData->getRate(),
                $previousMarketDateRateData->getRate()
            ),// preventing floating point precision problem
        ];
    }

    /**
     * @param string $date
     * @param string $currencyCode
     * @param string $baseCurrencyCode
     * @return string
     */
    private function getKey(string $date, string $currencyCode, string $baseCurrencyCode): string
    {
        $formattedDate = DateTimeImmutable::createFromFormat("d/m/Y", $date)->format("dmY");

        return $formattedDate . $currencyCode . $baseCurrencyCode;
    }
}