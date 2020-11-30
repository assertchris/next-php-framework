<?php

namespace Next\Concerns;

trait CannotBeCreated
{
    /**
     * @var mixed
     */
    private static $instance;

    /**
     * @return static
     */
    public static function getInstance()
    {
        if (!isset(static::$instance)) {
            /**
             * @phpstan-ignore-next-line
             */
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
