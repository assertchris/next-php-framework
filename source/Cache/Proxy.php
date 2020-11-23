<?php

namespace Next\Cache;

class Proxy
{
    use \Next\Concerns\CannotBeCreated;
    use \Next\Concerns\ForwardsToConnection;
    
    public static function connect(\Next\App $app)
    {
        // TODO: enable other drivers
        static::$connection = new \Symfony\Component\Cache\Adapter\FilesystemAdapter();
    }

    public function put(string $key, $value, $seconds = null): static
    {
        return $this->store($key, $value, $seconds, true);
    }

    private function store(string $key, $value, $seconds = null, $shouldOverride = true): static
    {
        $item = static::$connection->getItem($key);

        if (!$item->isHit() || ($item->isHit() && $shouldOverride)) {
            $item = $item->set($value);

            if (is_int($seconds)) {
                $item->expiresAfter($seconds);
            }

            static::$connection->save($item);
        }

        return $this;
    }

    public function add(string $key, $value, $seconds = null): static
    {
        return $this->store($key, $value, $seconds, false);
    }

    public function has(string $key): bool
    {
        $item = static::$connection->getItem($key);
        return $item->isHit();
    }

    public function get(string $key, $default = null): mixed
    {
        $item = static::$connection->getItem($key);

        if ($item->isHit()) {
            return $item->get();
        }

        if ($default instanceof \Closure) {
            return $default();
        }

        return $default;
    }

    public function remember(string $key, \Closure $factory, $seconds = null): static
    {
        return $this->store($key, $factory(), $seconds);
    }

    public function forget(string $key)
    {
        static::$connection->deleteItem($key);
    }

    public function flush()
    {
        static::$connection->clear();
    }
}
