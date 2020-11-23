<?php

namespace Next\Database;

class Proxy
{
    use \Next\Concerns\CannotBeCreated;
    use \Next\Concerns\ForwardsToConnection;

    public static function connect(\Next\App $app)
    {
        if (!isset($app['config']['database'])) {
            return;
        }

        $capsule = new \Illuminate\Database\Capsule\Manager();
        $capsule->addConnection($app['config']['database']);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();

        static::$connection = $capsule;
    }
}
