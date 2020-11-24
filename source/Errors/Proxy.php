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

        static::$connection->pushHandler(
            new \Whoops\Handler\CallbackHandler($this->getSafeErrorJsonCallback($app))
        );
    }

    private function getSafeErrorJsonCallback(\Next\App $app)
    {
        return function ($exception) use ($app) {
            if ($exception->getMessage() === '404' || $exception->getMessage() === '405') {
                $this->showSafeErrorJson($app, (int) $exception->getMessage());
            }

            $this->showSafeErrorJson($app, 500);
        };
    }

    private function showSafeErrorJson(\Next\App $app, int $code)
    {
        $app[\Next\Http\Response::class]->json([
            'status' => 'error',
            'code' => $code,
        ]);
    }

    public function enableHtmlHandler(\Next\App $app)
    {
        if (isset($app['config']['env']) && $app['config']['env'] === 'dev') {
            static::$connection->pushHandler(new \Whoops\Handler\PrettyPageHandler());
            return;
        }

        static::$connection->pushHandler(
            new \Whoops\Handler\CallbackHandler($this->getSafeErrorPageCallback($app))
        );
    }

    private function getSafeErrorPageCallback(\Next\App $app)
    {
        return function ($exception) use ($app) {
            if ($exception->getMessage() === '404' || $exception->getMessage() === '405') {
                $this->showSafeErrorPage($app, (int) $exception->getMessage());
            }

            $this->showSafeErrorPage($app, 500);
        };
    }

    private function showSafeErrorPage(\Next\App $app, int $code)
    {
        $path = $app['path.pages'];
        $request = $app[\Next\Http\Request::class];
        $response = $app[\Next\Http\Response::class];

        if (is_file("{$path}/_{$code}.php")) {
            $document = require "{$path}/_{$code}.php";
        } else {
            $document = require __DIR__ . "/../../pages/{$code}.php";
        }

        $response->setContent($document($request, $response));
        $response->send();
    }
}
