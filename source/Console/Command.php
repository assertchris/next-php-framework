<?php

namespace Next\Console;

abstract class Command extends \Symfony\Component\Console\Command\Command
{
    protected \Next\App $app;
    protected string $signature = '';
    protected string $description = '';
    protected string $help = '';
    protected \Symfony\Component\Console\Input\InputInterface $input;
    protected \Symfony\Component\Console\Output\OutputInterface $output;

    public function __construct()
    {
        [$name, $arguments, $options] = Parser::parse($this->signature);

        parent::__construct($name);

        $this->setHelp($this->help);
        $this->setDescription($this->description);
        $this->getDefinition()->addArguments($arguments);
        $this->getDefinition()->addOptions($options);
    }

    protected function execute(
        \Symfony\Component\Console\Input\InputInterface $input,
        \Symfony\Component\Console\Output\OutputInterface $output
    ) {
        $this->input = $input;
        $this->output = new \Symfony\Component\Console\Style\SymfonyStyle($input, $output);

        return (int) $this->app->call([$this, 'handle']);
    }

    public function setApp(\Next\App $app): static
    {
        $this->app = $app;
        return $this;
    }

    protected function hasArgument(string $name): bool
    {
        return $this->input->hasArgument($name);
    }

    protected function argument(string $key): null|array|string
    {
        return $this->input->getArgument($key);
    }

    /**
     * @return array<string, mixed>
     */
    protected function arguments(): array
    {
        return $this->input->getArguments();
    }

    protected function hasOption(string $name): bool
    {
        return $this->input->hasOption($name);
    }

    protected function option(string $name): null|bool|array|string
    {
        return $this->input->getOption($name);
    }

    /**
     * @return array<string, mixed>
     */
    protected function options(): array
    {
        return $this->input->getOptions();
    }

    protected function parseVerbosity(mixed $level = null): int
    {
        $levels = [
            'v' => \Symfony\Component\Console\Output\OutputInterface::VERBOSITY_VERBOSE,
            'vv' => \Symfony\Component\Console\Output\OutputInterface::VERBOSITY_VERY_VERBOSE,
            'vvv' => \Symfony\Component\Console\Output\OutputInterface::VERBOSITY_DEBUG,
            'quiet' => \Symfony\Component\Console\Output\OutputInterface::VERBOSITY_QUIET,
            'normal' => \Symfony\Component\Console\Output\OutputInterface::VERBOSITY_NORMAL,
        ];

        if (array_key_exists($level, $levels)) {
            return $levels[$level];
        }

        if (!is_int($level)) {
            return $levels['normal'];
        }

        return $level;
    }

    protected function confirm(string $question, bool $default = false): bool
    {
        /**
         * @phpstan-ignore-next-line
         */
        return $this->output->confirm($question, $default);
    }

    public function ask(string $question, mixed $default = null): mixed
    {
        /**
         * @phpstan-ignore-next-line
         */
        return $this->output->ask($question, $default);
    }

    protected function line(string $message, string $style = null, mixed $verbosity = null): void
    {
        if ($style) {
            $message = "<{$style}>$message</{$style}>";
        }

        $this->output->writeln($message, $this->parseVerbosity($verbosity));
    }

    protected function info(string $message, mixed $verbosity = null): void
    {
        $this->line($message, 'info', $verbosity);
    }

    protected function comment(string $message, mixed $verbosity = null): void
    {
        $this->line($message, 'comment', $verbosity);
    }

    protected function question(string $question, mixed $verbosity = null): void
    {
        $this->line($question, 'question', $verbosity);
    }

    protected function error(string $error, mixed $verbosity = null): void
    {
        $this->line($error, 'error', $verbosity);
    }

    public function warn(string $string, mixed $verbosity = null): void
    {
        $this->line($string, 'warning', $verbosity);
    }
}
