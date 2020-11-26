<?php

namespace Next\Errors;

class SafeErrorHtmlHandler
{
    public function __invoke(\Throwable $exception)
    {
        $app = \Next\App::getInstance();

        if ($exception->getMessage() === '404' || $exception->getMessage() === '405') {
            $this->showSafeErrorHtml($app, (int) $exception->getMessage());
        }

        $this->showSafeErrorHtml($app, 500);
    }

    private function showSafeErrorHtml(\Next\App $app, int $code)
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
