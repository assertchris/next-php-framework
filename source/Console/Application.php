<?php

namespace Next\Console;

class Application extends \Symfony\Component\Console\Application
{
    private \Next\App $app;

    public function __construct(\Next\App $app)
    {
        parent::__construct('Next Framework');

        (new \NunoMaduro\Collision\Provider())->register();

        $this->app = $app;
    }

    public function get(string $name): \Symfony\Component\Console\Command\Command
    {
        $command = parent::get($name);

        if ($command instanceof \Next\Console\Command) {
            $command->setApp($this->app);
        }

        return $command;
    }
}
