<?php

namespace Next\Concerns;

trait CannotBeCreated
{
    private static $instance;

    public static function instance()
    {
        if (!isset(static::$instance)) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    private function __construct()
    {
    }
}
