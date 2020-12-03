<?php

namespace Next\Errors;

class SafeErrorHtmlHandler
{
    public function __invoke(\Throwable $exception): void
    {
        $app = \Next\App::getInstance();

        if (in_array($exception->getMessage(), ['404', '405'])) {
            $this->showSafeErrorHtml($app, (int) $exception->getMessage());
            return;
        }

        $this->showSafeErrorHtml($app, 500);
    }

    private function showSafeErrorHtml(\Next\App $app, int $code): void
    {
        $path = $app['path.pages'];
        $request = $app[\Next\Http\Request::class];
        $response = $app[\Next\Http\Response::class];

        if (is_file("{$path}/_{$code}.php")) {
            $document = require "{$path}/_{$code}.php";
        } else {
            $document = require __DIR__ . "/../../pages/_{$code}.php";
        }

        $response->setContent($app->call($document));
        $response->send();
    }
}
