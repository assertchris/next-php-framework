<?php

namespace Next\Middleware;

class CookieMiddleware
{
    public function handle(\Next\Http\Request $request, callable $next): mixed
    {
        $response = $next($request);

        $app = \Next\App::getInstance();
        $cookies = $app[\Next\Cookie::class];

        foreach ($cookies->getQueuedCookies() as $cookie) {
            $response->headers->setCookie($cookie);
        }

        return $response;
    }
}
