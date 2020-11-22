<?php

namespace Next\Session;

class Proxy
{
    use \Next\Concerns\CannotBeCreated;
    use \Next\Concerns\ForwardsToConnection;
    
    public static function connect(\Next\App $app)
    {
        // TODO: enable other drivers
        static::$connection = new \Symfony\Component\HttpFoundation\Session\Session();

        $app['session'] = static::getInstance();
    }
}
