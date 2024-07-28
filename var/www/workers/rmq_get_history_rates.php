<?php

declare(strict_types=1);

use CbrCurrencyRates\RMQ\RMQ;
use CbrCurrencyRates\RMQ\Task\GetRatesByDateTask;

require_once __DIR__ . '/../vendor/autoload.php';

RMQ::getTask([GetRatesByDateTask::class, 'run']);