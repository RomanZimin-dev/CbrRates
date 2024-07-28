<?php

declare(strict_types=1);

namespace CbrCurrencyRates\RMQ\Task;

use CbrCurrencyRates\CbrCurrencyRates;
use DI\ContainerBuilder;
use PhpAmqpLib\Message\AMQPMessage;

class GetRatesByDateTask
{
    /**
     * @param \PhpAmqpLib\Message\AMQPMessage $msg
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public static function run(AMQPMessage $msg)
    {
        $params = json_decode($msg->getBody(), true);

        $containerBuilder = new ContainerBuilder();
        $container = $containerBuilder->build();
        $cbrCurrencyRates = $container->get(CbrCurrencyRates::class);

        $cbrCurrencyRates->getCrossRateData(
            $params["date"],
            $params["currencyCode"],
            $params["baseCurrencyCode"]
        );

        echo "Cached: " . $params["date"] . " " . $params["currencyCode"] . $params["baseCurrencyCode"] . "\n";
    }
}