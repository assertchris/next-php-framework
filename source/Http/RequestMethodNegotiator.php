<?php

namespace Next\Http;

class RequestMethodNegotiator
{
    /**
     * @var array<string, callable>
     */
    protected array $handlers = [];

    public const METHODS = ['get', 'post', 'patch', 'put', 'delete'];

    /**
     * @return static
     */
    private function handle(string $method, \Closure $then)
    {
        $this->handlers[$method] = $then;
        return $this;
    }

    /**
     * @param array<mixed> $params
     *
     * @return static
     */
    public function __call(string $method, array $params = [])
    {
        if (in_array($method, static::METHODS) || $method === 'default') {
            return $this->handle($method, ...$params);
        }

        throw new \InvalidArgumentException("Unsupported method {$method}");
    }

    public function negotiate(): mixed
    {
        $request = \Next\App::getInstance()->make(\Next\Http\Request::class);
        $currentMethod = strtolower($request->method());

        foreach (static::METHODS as $method) {
            if (isset($this->handlers[$method]) && $currentMethod === $method) {
                return $this->handlers[$method]();
            }
        }

        if (isset($this->handlers['default'])) {
            return $this->handlers['default']();
        }

        throw new \RuntimeException("No content negotiator for {$currentMethod}");
    }
}
