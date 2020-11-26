<?php

namespace Next\Errors;

class SafeErrorJsonHandler
{
    public function __invoke(\Throwable $exception)
    {
        $app = \Next\App::getInstance();

        if ($exception->getMessage() === '404' || $exception->getMessage() === '405') {
            $this->showSafeErrorJson($app, (int) $exception->getMessage());
        }

        $this->showSafeErrorJson($app, 500);
    }

    private function showSafeErrorJson(\Next\App $app, int $code)
    {
        $app[\Next\Http\Response::class]->json([
            'status' => 'error',
            'code' => $code,
        ]);
    }
}
