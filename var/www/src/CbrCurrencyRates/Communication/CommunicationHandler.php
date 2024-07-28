<?php

declare(strict_types=1);

namespace CbrCurrencyRates\Communication;

use CbrCurrencyRates\Communication\Builder\RequestBuilder;
use CbrCurrencyRates\Communication\Builder\ResponseBuilder;
use CbrCurrencyRates\Communication\Exception\BadCurrencyException;
use CbrCurrencyRates\Communication\Exception\BadResponseException;
use CbrCurrencyRates\Communication\Exception\CommunicationException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class CommunicationHandler
{
    private const ENDPOINT = "https://www.cbr.ru/scripts/XML_daily.asp";

    private RequestBuilder $requestBuilder;
    private ResponseBuilder $responseBuilder;
    private Client $client;

    /**
     * CommunicationHandler constructor.
     * @param RequestBuilder $requestBuilder
     * @param ResponseBuilder $responseBuilder
     * @param Client $client
     */
    public function __construct(RequestBuilder $requestBuilder, ResponseBuilder $responseBuilder, Client $client)
    {
        $this->requestBuilder = $requestBuilder;
        $this->responseBuilder = $responseBuilder;
        $this->client = $client;
    }

    /**
     * @param string $date
     * @param string $currencyCode
     * @param string $baseCurrencyCode
     * @return CbrResponse
     * @throws BadResponseException
     * @throws CommunicationException
     * @throws BadCurrencyException
     * @throws GuzzleException
     */
    public function sendRequest(string $date, string $currencyCode, string $baseCurrencyCode): CbrResponse
    {
        $cbrRequest = $this->requestBuilder->buildRequest($date);

        $response = $this->client->request(
            "GET",
            self::ENDPOINT . "?" . $cbrRequest->getAsParams()
        );

        return $this->responseBuilder->buildResponse($response, $currencyCode, $baseCurrencyCode);
    }
}