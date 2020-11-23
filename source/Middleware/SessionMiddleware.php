<?php

namespace Next\Middleware;

class SessionMiddleware
{
    public function handle(\Next\App $app, \Next\Http\Request $request, \Next\Http\Response $response)
    {
        $app[\Next\Session::class]->start();
    }
}
