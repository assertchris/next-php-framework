<?php

namespace Next\Session;

/**
 * @method \Symfony\Component\HttpFoundation\Session\SessionInterface start()
 */
class Proxy
{
    use \Next\Concerns\CannotBeCreated;
    use \Next\Concerns\ForwardsToConnection;

    public static function connect(\Next\App $app): void
    {
        // TODO: enable other drivers
        static::$connection = new \Symfony\Component\HttpFoundation\Session\Session();
    }
}
