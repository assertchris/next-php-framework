<?php

namespace Next\Errors;

class Proxy
{
    use \Next\Concerns\CannotBeCreated;
    use \Next\Concerns\ForwardsToConnection;

    public static function connect(\Next\App $app)
    {
        $whoops = new \Whoops\Run();
        $whoops->register();

        static::$connection = $whoops;
    }

    public function enableJsonHandler(\Next\App $app)
    {
        if (isset($app['config']['env']) && $app['config']['env'] === 'dev') {
            static::$connection->pushHandler(new \Whoops\Handler\JsonResponseHandler());
            return;
        }

        static::$connection->pushHandler(new \Whoops\Handler\CallbackHandler(new \Next\Errors\SafeErrorJsonHandler()));
    }

    public function enableHtmlHandler(\Next\App $app)
    {
        if (isset($app['config']['env']) && $app['config']['env'] === 'dev') {
            static::$connection->pushHandler(new \Whoops\Handler\PrettyPageHandler());
            return;
        }

        static::$connection->pushHandler(new \Whoops\Handler\CallbackHandler(new \Next\Errors\SafeErrorHtmlHandler()));
    }
}
