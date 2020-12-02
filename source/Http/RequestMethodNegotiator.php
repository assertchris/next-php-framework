<?php

namespace Next\Http;

class RequestMethodNegotiator
{
    /** @var array<string, \Closure> */
    protected array $handlers = [
        'GET' => null,
        'POST' => null,
        'PATCH' => null,
        'PUT' => null,
        'DELETE' => null,
        'OPTIONS' => null,
    ];

    private ?\Closure $default = null;

    public function __construct()
    {
        $this->when('options', fn() => $this->defaultOptionsHandler());
    }

    /**
     * @return static
     */
    private function when(string $method, \Closure $handler): mixed
    {
        $this->handlers[strtoupper($method)] = $handler;

        return $this;
    }

    /**
     * @return static
     */
    public function get(\Closure $handler): mixed
    {
        return $this->when('get', $handler);
    }

    /**
     * @return static
     */
    public function post(\Closure $handler): mixed
    {
        return $this->when('post', $handler);
    }

    /**
     * @return static
     */
    public function patch(\Closure $handler): mixed
    {
        return $this->when('patch', $handler);
    }

    /**
     * @return static
     */
    public function put(\Closure $handler): mixed
    {
        return $this->when('put', $handler);
    }

    /**
     * @return static
     */
    public function delete(\Closure $handler): mixed
    {
        return $this->when('delete', $handler);
    }

    /**
     * @return static
     */
    public function options(\Closure $handler): mixed
    {
        return $this->when('options', $handler);
    }

    /**
     * @return static
     */
    public function default(\Closure $handler): mixed
    {
        $this->default = $handler;

        return $this;
    }

    /**
     * @return \Next\Http\Response|\Next\Http\JsonResponse|\Next\Http\RedirectResponse
     */
    private function defaultOptionsHandler(): mixed
    {
        return response()
            ->setStatusCode(204)
            ->header('Allow', implode(', ', array_keys(array_filter($this->handlers))));
    }

    public function negotiate(string $method): mixed
    {
        $method = strtoupper($method);

        if (!empty($this->handlers[$method])) {
            return $this->handlers[$method]();
        }

        if ($this->default !== null) {
            return ($this->default)();
        }

        throw new \RuntimeException("No content negotiator for {$method}");
    }
}
