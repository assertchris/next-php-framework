<?php

namespace Next\Console;

use Next\App;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class Command extends \Symfony\Component\Console\Command\Command
{
    protected App $app;

    protected string $signature = '';

    protected string $description = '';

    protected string $help = '';

    protected InputInterface $input;

    protected OutputInterface $output;

    public function __construct()
    {
        [$name, $arguments, $options] = Parser::parse($this->signature);

        parent::__construct($name);

        $this->setHelp($this->help);
        $this->setDescription($this->description);

        $this->getDefinition()->addArguments($arguments);
        $this->getDefinition()->addOptions($options);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;

        $this->output = new SymfonyStyle($input, $output);

        return (int) $this->app->call([$this, 'handle']);
    }

    public function setApp(App $app)
    {
        $this->app = $app;
    }

    protected function hasArgument(string $name): bool
    {
        return $this->input->hasArgument($name);
    }

    protected function argument(string $key): string|array|null
    {
        return $this->input->getArgument($key);
    }

    protected function arguments(): array
    {
        return $this->input->getArguments();
    }

    protected function hasOption(string $name): bool
    {
        return $this->input->hasOption($name);
    }

    protected function option(string $name): string|array|bool|null
    {
        return $this->input->getOption($name);
    }

    protected function options(): array
    {
        return $this->input->getOptions();
    }

    protected function parseVerbosity(int|string|null $level = null): int
    {
        $levels = [
            'v' => OutputInterface::VERBOSITY_VERBOSE,
            'vv' => OutputInterface::VERBOSITY_VERY_VERBOSE,
            'vvv' => OutputInterface::VERBOSITY_DEBUG,
            'quiet' => OutputInterface::VERBOSITY_QUIET,
            'normal' => OutputInterface::VERBOSITY_NORMAL,
        ];

        if (array_key_exists($level, $levels)) {
            return $levels[$level];
        }

        if (! is_int($level)) {
            return $levels['normal'];
        }

        return $level;
    }

    protected function confirm(string $question, bool $default = false): bool
    {
        return $this->output->confirm($question, $default);
    }

    public function ask(string $question, string|null $default = null): mixed
    {
        return $this->output->ask($question, $default);
    }

    protected function line(string $message, string $style = null, string|int|null $verbosity = null): void
    {
        $message = $style ? "<$style>$message</$style>" : $message;

        $this->output->writeln($message, $this->parseVerbosity($verbosity));
    }

    protected function info(string $message, string|int $verbosity = null): void
    {
        $this->line($message, 'info', $verbosity);
    }

    protected function comment(string $message, string|int $verbosity = null): void
    {
        $this->line($message, 'comment', $verbosity);
    }

    protected function question(string $question, string|int $verbosity = null): void
    {
        $this->line($question, 'question', $verbosity);
    }

    protected function error(string $error, string|int $verbosity = null): void
    {
        $this->line($error, 'error', $verbosity);
    }

    public function warn(string $string, string|int $verbosity = null): void
    {
        $this->line($string, 'warning', $verbosity);
    }
}
