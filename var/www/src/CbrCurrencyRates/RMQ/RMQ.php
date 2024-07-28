<?php

declare(strict_types=1);

namespace CbrCurrencyRates\RMQ;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RMQ {
    private const RMQ_HOST = "rabbitmq";
    private const RMQ_PORT = 5672;
    private const RMQ_USER = "guest";
    private const RMQ_PASSWORD = "guest";
    private const RMQ_QUEUE_NAME = "get_history_rates";

    private AMQPStreamConnection $connection;
    private AMQPChannel $channel;

    private static ?RMQ $instance = null;

    private function __construct(AMQPStreamConnection $connection, AMQPChannel $channel) {
        $this->connection = $connection;
        $this->channel = $channel;
    }

    /**
     * @return RMQ|null
     */
    private static function getInstance(): ?RMQ
    {
        if (self::$instance === null) {
            $connection = new AMQPStreamConnection(
                self::RMQ_HOST,
                self::RMQ_PORT,
                self::RMQ_USER,
                self::RMQ_PASSWORD

            );
            $channel = $connection->channel();
            $channel->queue_declare(self::RMQ_QUEUE_NAME, false, false, false, false);

            self::$instance = new self($connection, $channel);
        }

        return self::$instance;
    }

    /**
     * @param array $data
     */
    public static function addTask(array $data): void
    {
        $msg = new AMQPMessage(json_encode($data));
        self::getInstance()->channel->basic_publish($msg, '', self::RMQ_QUEUE_NAME);
    }

    /**
     * @param callable $function
     */
    public static function getTask(callable $function): void
    {
        self::getInstance()->channel->basic_consume(self::RMQ_QUEUE_NAME, '', false, true, false, false, $function);

        while (self::getInstance()->channel->is_consuming()) {
            self::getInstance()->channel->wait();
        }
    }

    public static function close(): void
    {
        self::getInstance()->channel->close();
        self::getInstance()->connection->close();
    }
}