<?php

declare(strict_types=1);

namespace CbrCurrencyRates\Communication;

class CbrRequest
{
    private string $date;

    /**
     * Request constructor.
     * @param string $date
     */
    public function __construct(string $date)
    {
        $this->date = $date;
    }

    /**
     * @return string
     */
    public function getDate(): string
    {
        return $this->date;
    }

    /**
     * @return string
     */
    public function getAsParams(): string
    {
        return "date_req=" . $this->getDate();
    }
}