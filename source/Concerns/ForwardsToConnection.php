<?php

namespace Next\Concerns;

trait ForwardsToConnection
{
    private static $connection;

    public function __call(string $method, array $params = [])
    {
        return static::$connection->$method(...$params);
    }

    public static function __callStatic(string $method, array $params = [])
    {
        return static::$connection->$method(...$params);
    }
}
