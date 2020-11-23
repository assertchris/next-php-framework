<?php

namespace Next\Logging;

class Proxy
{
    use \Next\Concerns\CannotBeCreated;
    use \Next\Concerns\ForwardsToConnection;
    
    public static function connect(\Next\App $app)
    {
        $logger = new \Illuminate\Log\Logger(new \Monolog\Logger('Next Logger'));
        $logger->pushHandler(new \Monolog\Handler\StreamHandler(path('log')));

        static::$connection = $logger;
    }
}
