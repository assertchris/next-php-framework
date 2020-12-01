<?php

namespace Next\Cookie;

use Illuminate\Cookie\CookieJar;

class Proxy
{
    use \Next\Concerns\CannotBeCreated;
    use \Next\Concerns\ForwardsToConnection;

    private static $config;
    private static $request;

    public static function connect(\Next\App $app): void
    {
        $config = [
            'path' => '/',
            'domain' => null,
            'secure' => null,
            'sameSite' => null,
        ];

        if (isset($app['config']['cookie'])) {
            $config = array_merge($config, $app['config']['cookie']);
        }

        $cookies = new CookieJar();
        $cookies->setDefaultPathAndDomain($config['path'], $config['domain'], $config['secure'], $config['sameSite']);

        static::$config = $config;
        static::$connection = $cookies;

        static::$request = $app[\Next\Http\Request::class];
    }

    public function has(string $key): bool
    {
        // i would love to use Symfony's request cookies but they're not working and i don't know why
        return $this->hasQueued($key, static::$config['path']) || !empty($_COOKIE[$key]);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        if ($this->hasQueued($key, static::$config['path'])) {
            return $this->queued($key, $default, static::$config['path'])->getValue();
        }

        // i would love to use Symfony's request cookies but they're not working and i don't know why
        if (!empty($_COOKIE[$key])) {
            return $_COOKIE[$key];
        }

        return $default;
    }

    /**
     * @return static
     */
    public function put(
        $name,
        $value,
        $minutes = 2628000,
        $path = null,
        $domain = null,
        $secure = null,
        $httpOnly = true,
        $raw = false,
        $sameSite = null
    ): mixed {
        $this->queue($name, $value, $minutes, $path, $domain, $secure, $httpOnly, $raw, $sameSite);
        return $this;
    }
}
