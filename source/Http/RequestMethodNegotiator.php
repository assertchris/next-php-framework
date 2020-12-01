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
        $this->when('options', fn () => $this->defaultOptionsHandler());
    }

    private function when(string $method, \Closure $handler): static
    {
        $this->handlers[strtoupper($method)] = $handler;

        return $this;
    }

    public function get(\Closure $handler): static
    {
        return $this->when('get', $handler);
    }

    public function post(\Closure $handler): static
    {
        return $this->when('post', $handler);
    }

    public function patch(\Closure $handler): static
    {
        return $this->when('patch', $handler);
    }

    public function put(\Closure $handler): static
    {
        return $this->when('put', $handler);
    }

    public function delete(\Closure $handler): static
    {
        return $this->when('delete', $handler);
    }

    public function options(\Closure $handler): static
    {
        return $this->when('options', $handler);
    }

    public function default(\Closure $handler): static
    {
        $this->default = $handler;

        return $this;
    }

    private function defaultOptionsHandler(): \Next\Http\Response
    {
        return response()
            ->setStatusCode(204)
            ->header(
                'Allow',
                implode(', ', array_keys(array_filter($this->handlers))),
            );
    }

    public function negotiate(string $method): mixed
    {
        $method = strtoupper($method);

        if (! empty($this->handlers[$method])) {
            return $this->handlers[$method]();
        }

        if ($this->default !== null) {
            return ($this->default)();
        }

        throw new \RuntimeException("No content negotiator for {$method}");
    }
}
