<?php

namespace Next\Middleware;

class SessionMiddleware
{
    private \Next\Session $session;

    public function __construct(\Next\Session $session)
    {
        $this->session = $session;
    }

    public function handle(\Next\Http\Request $request, callable $next): mixed
    {
        $this->session->start();

        return $next($request);
    }
}
