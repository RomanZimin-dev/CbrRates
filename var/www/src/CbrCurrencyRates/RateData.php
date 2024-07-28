<?php

declare(strict_types=1);

namespace CbrCurrencyRates;

class RateData
{
    private float $rate;
    private string $previousMarketDate;

    /**
     * RateData constructor.
     * @param float $rate
     * @param string $previousMarketDate
     */
    public function __construct(float $rate, string $previousMarketDate)
    {
        $this->rate = $rate;
        $this->previousMarketDate = $previousMarketDate;
    }

    /**
     * @return float
     */
    public function getRate(): float
    {
        return round($this->rate, CbrCurrencyRates::DEFAULT_ROUND_PRECISION_FOR_CROSS_RATES);
    }

    /**
     * @return string
     */
    public function getPreviousMarketDate(): string
    {
        return $this->previousMarketDate;
    }

    /**
     * @return array
     */
    public function getAsArray(): array
    {
        return [
            'rate' => $this->getRate(),
            'previousMarketDate' => $this->getPreviousMarketDate()
        ];
    }

    /**
     * @return string
     */
    public function getAsJson(): string
    {
        return (string) json_encode($this->getAsArray());
    }
}