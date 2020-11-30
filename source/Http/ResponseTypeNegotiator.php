<?php

namespace Next\Http;

class ResponseTypeNegotiator
{
    /** @var array<string, \Closure> */
    private array $handlers = [];

    private ?\Closure $fallback = null;

    public function __call(string $method, array $arguments): self
    {
        $this->when($method, $arguments[0]);

        return $this;
    }

    public function when(string $extension, \Closure $handler): self
    {
        $this->handlers[$extension] = $handler;

        return $this;
    }

    public function fallback(\Closure $handler): self
    {
        $this->fallback = $handler;

        return $this;
    }

    public function negotiate(string $extension): mixed
    {
        if (array_key_exists($extension, $this->handlers)) {
            return $this->handlers[$extension]();
        }

        if ($this->fallback !== null) {
            return ($this->fallback)();
        }

        throw new \RuntimeException("File extension {$extension} not supported");
    }
}
