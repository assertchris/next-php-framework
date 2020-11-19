<?php

namespace Next;

use Illuminate\Log\Logger as IlluminateLogger;
use Monolog\Logger as MonologLogger;
use Monolog\Handler\StreamHandler;
use Next\Concerns\CannotBeCreated;
use Next\Http\Request;

class Log
{
    use CannotBeCreated;

    private IlluminateLogger $logger;

    private function __construct()
    {
        $logger = new IlluminateLogger(new MonologLogger('Next Logger'));
        $logger->pushHandler(new StreamHandler(path('log')));

        $this->logger = $logger;
    }

    public function __call(string $method, $params)
    {
        return $this->logger->$method(...$params);
    }

    public static function __callStatic(string $method, $params)
    {
        return static::instance()->$method(...$params);
    }
}
