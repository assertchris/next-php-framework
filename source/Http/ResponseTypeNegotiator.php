<?php

namespace Next\Http;

class ResponseTypeNegotiator
{
    protected array $handlers = [];
    public const TYPES = ['html', 'json', 'xml', 'csv'];

    private function handle(string $type, \Closure $then): static
    {
        $this->handlers[$type] = $then;
        return $this;
    }

    public function __call(string $type, array $params = []): static
    {
        if (in_array($type, static::TYPES) || $type === 'default') {
            return $this->handle($type, ...$params);
        }

        throw new \InvalidArgumentException("Unsupported type {$type}");
    }

    public function negotiate(): mixed
    {
        $request = \Next\App::getInstance()->make(\Next\Http\Request::class);
        $currentType = strtolower($request->getPathInfoExtension());

        foreach (static::TYPES as $type) {
            if (isset($this->handlers[$type]) && $currentType === $type) {
                return $this->handlers[$type]();
            }
        }

        if (isset($this->handlers['default'])) {
            return $this->handlers['default']();
        }

        throw new \RuntimeException("No content negotiator for {$currentType}");
    }
}
