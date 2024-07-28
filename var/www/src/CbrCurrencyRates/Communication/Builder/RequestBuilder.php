<?php

declare(strict_types=1);

namespace CbrCurrencyRates\Communication\Builder;

use CbrCurrencyRates\Communication\CbrRequest;

class RequestBuilder
{
    /**
     * @param string $date
     * @return CbrRequest
     */
    public function buildRequest(string $date): CbrRequest
    {
        return new CbrRequest($date);
    }
}