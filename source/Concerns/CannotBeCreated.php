<?php

namespace Next\Concerns;

trait CannotBeCreated
{
    private static $instance;

    public static function getInstance()
    {
        if (!isset(static::$instance)) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    private function __construct()
    {
    }

    private function __clone()
    {
    }
}
