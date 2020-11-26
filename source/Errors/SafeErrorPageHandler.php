<?php

namespace Next\Errors;

class SafeErrorPageHandler
{
    public function __invoke(\Throwable $exception)
    {
        $app = \Next\App::getInstance();

        if ($exception->getMessage() === '404' || $exception->getMessage() === '405') {
            $this->showSafeErrorPage($app, (int) $exception->getMessage());
        }

        $this->showSafeErrorPage($app, 500);
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
