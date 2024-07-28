<?php

declare(strict_types=1);

namespace CbrCurrencyRates\Cache;

use Memcached;

class Cache {
    private const MEMCACHE_HOST = "memcache";
    private const MEMCACHE_PORT = 11211;
    private const CACHING_TIME_SEC = 60 * 60; // 1 hour

    private Memcached $memcached;
    private static ?Cache $instance = null;

    private function __construct(Memcached $memcached) {
        $this->memcached = $memcached;
    }

    /**
     * @return Cache|null
     */
    private static function getInstance() {
        if (self::$instance === null) {
            $memcache = new Memcached();
            $memcache->addServer(self::MEMCACHE_HOST, self::MEMCACHE_PORT);

            self::$instance = new self($memcache);
        }

        return self::$instance;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public static function get(string $key): mixed
    {
        return self::getInstance()->memcached->get($key);
    }

    /**
     * @param string $key
     * @param $data
     * @return bool
     */
    public static function set(string $key, $data): bool
    {
        return self::getInstance()->memcached->set($key, $data, self::CACHING_TIME_SEC);
    }

    /**
     * @return bool
     */
    public static function flush(): bool
    {
        return self::getInstance()->memcached->flush();
    }
}