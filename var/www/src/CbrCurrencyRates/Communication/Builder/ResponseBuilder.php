<?php

declare(strict_types=1);

namespace CbrCurrencyRates\Communication\Builder;

use CbrCurrencyRates\Communication\CbrResponse;
use CbrCurrencyRates\Communication\Exception\BadCurrencyException;
use CbrCurrencyRates\Communication\Exception\BadResponseException;
use CbrCurrencyRates\Communication\Exception\CommunicationException;
use CbrCurrencyRates\Communication\Validator\ResponseDataValidator;
use DateTimeImmutable;
use Psr\Http\Message\ResponseInterface;

class ResponseBuilder
{
    private ResponseDataValidator $responseDataValidator;

    /**
     * ResponseBuilder constructor.
     * @param ResponseDataValidator $responseDataValidator
     */
    public function __construct(ResponseDataValidator $responseDataValidator)
    {
        $this->responseDataValidator = $responseDataValidator;
    }

    /**
     * @param ResponseInterface $response
     * @param string $currencyCode
     * @param string $baseCurrencyCode
     * @return CbrResponse
     * @throws BadResponseException
     * @throws CommunicationException
     * @throws BadCurrencyException
     */
    public function buildResponse(ResponseInterface $response, string $currencyCode, string $baseCurrencyCode): CbrResponse
    {
        $this->responseDataValidator->validate($response);

        $responseBody = $response->getBody()->getContents();
        $xmlObject = simplexml_load_string($responseBody);

        $rateDate = DateTimeImmutable::createFromFormat("d.m.Y", (string) $xmlObject['Date']);
        $data = [];
        $valutes = $xmlObject->Valute;

        foreach ($valutes as $valute) {
            $data[(string) $valute->CharCode] = (float) str_replace(',', '.', (string) $valute->VunitRate);
        }
        $cbrResponse = new CbrResponse($rateDate, $data);

        $this->responseDataValidator->validateCurrencyExists($cbrResponse, $currencyCode, $baseCurrencyCode);

        return $cbrResponse;
    }
}