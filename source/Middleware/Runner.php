<?php

namespace Next\Middleware;

class Runner
{
    private \Next\App $app;
    private array $middleware;
    private \Closure $final;

    public function __construct(\Next\App $app, array $middleware, \Closure $final)
    {
        $this->app = $app;
        $this->middleware = $middleware;
        $this->final = $final;
    }

    /**
     * @return \Next\Http\Response|\Next\Http\JsonResponse|\Next\Http\RedirectResponse
     */
    public function __invoke(\Next\Http\Request $request): mixed
    {
        if (empty($this->middleware)) {
            return ($this->final)($request);
        }

        $current = array_shift($this->middleware);
        $next = clone $this;

        return $this->app->make($current)->handle($request, $next);
    }
}
