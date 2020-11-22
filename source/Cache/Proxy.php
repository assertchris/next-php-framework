<?php

namespace Next\Cache;

class Proxy
{
    use \Next\Concerns\CannotBeCreated;
    use \Next\Concerns\ForwardsToConnection;
    
    public static function connect(\Next\App $app)
    {
        // TODO: enable other drivers
        static::$connection = new \Symfony\Component\Cache\Adapter\FilesystemAdapter();

        $app['cache'] = static::getInstance();
    }
}
