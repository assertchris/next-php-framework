<?php

namespace Next;

use Next\Concerns\CannotBeCreated;
use Symfony\Component\HttpFoundation\Session\Session as SymfonySession;

class Session
{
    use CannotBeCreated;

    private SymfonySession $session;

    private function __construct()
    {
        $this->session = new SymfonySession();
    }

    public function __call(string $method, $params)
    {
        return $this->session->$method(...$params);
    }

    public static function __callStatic(string $method, $params)
    {
        return static::instance()->$method(...$params);
    }
}
