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

    public function put(string $key, mixed $value, int $seconds = null)
    {
        return $this->store($key, $value, $seconds, true);
    }

    private function store(string $key, mixed $value, int $seconds = null, bool $shouldOverride = true)
    {
        $item = static::$connection->getItem($key);

        if (!$item->isHit() || ($item->isHit() && $shouldOverride)) {
            $item->set($value);

            if (is_int($seconds)) {
                $item->expiresAfter($seconds);
            }

            static::$connection->save($item);
        }

        return $this;
    }

    public function add(string $key, mixed $value, int $seconds = null)
    {
        return $this->store($key, $value, $seconds, false);
    }

    public function has(string $key): bool
    {
        $item = static::$connection->getItem($key);
        return $item->isHit();
    }

    public function get(string $key, mixed $default = null): mixed
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

    public function remember(string $key, \Closure $factory, int $seconds = null): mixed
    {
        $this->store($key, $value = $factory(), $seconds);
        return $value;
    }

    public function forget(string $key)
    {
        static::$connection->deleteItem($key);
        return $this;
    }

    public function flush()
    {
        static::$connection->clear();
        return $this;
    }
}
