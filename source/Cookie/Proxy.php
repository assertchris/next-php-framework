<?php

namespace Next\Cookie;

use Illuminate\Cookie\CookieJar;

/**
 * @method bool hasQueued(string $key, string $path = null)
 * @method \Symfony\Component\HttpFoundation\Cookie|null queued(string $key, mixed $default = null, string $path = null)
 * @method void queue(mixed ...$params)
 */
class Proxy
{
    use \Next\Concerns\CannotBeCreated;
    use \Next\Concerns\ForwardsToConnection;

    /**
     * @var array<string, mixed>
     */
    private static array $config;

    private static \Next\Http\Request $request;

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
     * @param bool|null $secure
     * @param string|null $sameSite
     *
     * @return static
     */
    public function put(
        string $name,
        mixed $value,
        int $minutes = 2628000,
        string $path = null,
        string $domain = null,
        mixed $secure = null,
        bool $httpOnly = true,
        bool $raw = false,
        mixed $sameSite = null
    ): mixed {
        $this->queue($name, $value, $minutes, $path, $domain, $secure, $httpOnly, $raw, $sameSite);
        return $this;
    }
}
