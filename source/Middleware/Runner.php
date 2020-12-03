<?php

namespace Next\Middleware;

class Runner
{
    private \Next\App $app;

    /**
     * @var array<callable>
     */
    private array $middleware;

    private \Closure $final;

    /**
     * @param \Next\App       $app
     * @param array<callable> $middleware
     * @param \Closure        $final
     */
    public function __construct(\Next\App $app, array $middleware, \Closure $final)
    {
        $this->app = $app;
        $this->middleware = $middleware;
        $this->final = $final;
    }

    public function __invoke(\Next\Http\Request $request): \Next\Http\Response|\Next\Http\JsonResponse|\Next\Http\RedirectResponse
    {
        if (empty($this->middleware)) {
            return ($this->final)($request);
        }

        $current = array_shift($this->middleware);
        $next = clone $this;

        if (!is_object($current)) {
            $current = $this->app->make($current);
        }

        return $current->handle($request, $next);
    }
}
