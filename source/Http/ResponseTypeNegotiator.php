<?php

namespace Next\Http;

class ResponseTypeNegotiator
{
    /** @var array<string, \Closure> */
    private array $handlers = [];

    private ?\Closure $default = null;

    public function __call(string $method, array $arguments): self
    {
        $this->when($method, array_shift($arguments));

        return $this;
    }

    public function when(string $extension, \Closure $handler): self
    {
        $this->handlers[$extension] = $handler;

        return $this;
    }

    public function default(\Closure $handler): self
    {
        $this->default = $handler;

        return $this;
    }

    public function negotiate(string $extension): mixed
    {
        if (array_key_exists($extension, $this->handlers)) {
            return $this->handlers[$extension]();
        }

        if ($this->default !== null) {
            return ($this->default)();
        }

        throw new \RuntimeException("File extension {$extension} not supported");
    }
}
