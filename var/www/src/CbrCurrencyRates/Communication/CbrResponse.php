<?php

declare(strict_types=1);

namespace CbrCurrencyRates\Communication;

use DateTimeImmutable;

class CbrResponse
{
    private DateTimeImmutable $rateDate;

    // array ('CurrencyCodeRUR' => $rate)
    private array $data;

    /**
     * CbrResponse constructor.
     * @param DateTimeImmutable $rateDate
     * @param array $data
     */
    public function __construct(DateTimeImmutable $rateDate, array $data)
    {
        $this->rateDate = $rateDate;
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getRateDate(): DateTimeImmutable
    {
        return $this->rateDate;
    }
}