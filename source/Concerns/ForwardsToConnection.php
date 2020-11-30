<?php

namespace Next\Concerns;

trait ForwardsToConnection
{
    /**
     * @var mixed
     */
    private static $connection;

    /**
     * @param array<mixed> $params
     */
    public function __call(string $method, array $params = []): mixed
    {
        return static::$connection->$method(...$params);
    }

    /**
     * @param array<mixed> $params
     */
    public static function __callStatic(string $method, array $params = []): mixed
    {
        return static::$connection->$method(...$params);
    }
}
