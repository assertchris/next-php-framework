<?php

namespace Next\Console;

/**
 * Based on Laravel 8's console component.
 */
class Parser
{
    public static function parse(string $expression): array
    {
        $name = self::name($expression);

        if (preg_match_all('/{\s*(.*?)\s*}/', $expression, $matches)) {
            if (count($matches[1])) {
                return array_merge([$name], self::parameters($matches[1]));
            }
        }

        return [$name, [], []];
    }

    protected static function name(string $expression): string
    {
        if (!preg_match('/[^\s]+/', $expression, $matches)) {
            throw new \InvalidArgumentException('Unable to determine command name from signature.');
        }

        return $matches[0];
    }

    protected static function parameters(array $tokens): array
    {
        $arguments = [];

        $options = [];

        foreach ($tokens as $token) {
            if (preg_match('/-{2,}(.*)/', $token, $matches)) {
                $options[] = self::parseOption($matches[1]);
            } else {
                $arguments[] = self::parseArgument($token);
            }
        }

        return [$arguments, $options];
    }

    protected static function parseArgument(string $token): \Symfony\Component\Console\Input\InputArgument
    {
        [$token, $description] = self::extractDescription($token);

        switch (true) {
            case str_ends_with($token, '?*'):
                return new \Symfony\Component\Console\Input\InputArgument(
                    trim($token, '?*'),
                    \Symfony\Component\Console\Input\InputArgument::IS_ARRAY,
                    $description
                );

            case str_ends_with($token, '*'):
                return new \Symfony\Component\Console\Input\InputArgument(
                    trim($token, '*'),
                    \Symfony\Component\Console\Input\InputArgument::IS_ARRAY |
                        \Symfony\Component\Console\Input\InputArgument::REQUIRED,
                    $description
                );

            case str_ends_with($token, '?'):
                return new \Symfony\Component\Console\Input\InputArgument(
                    trim($token, '?'),
                    \Symfony\Component\Console\Input\InputArgument::OPTIONAL,
                    $description
                );

            case preg_match('/(.+)=\*(.+)/', $token, $matches):
                return new \Symfony\Component\Console\Input\InputArgument(
                    $matches[1],
                    \Symfony\Component\Console\Input\InputArgument::IS_ARRAY,
                    $description,
                    preg_split('/,\s?/', $matches[2])
                );

            case preg_match('/(.+)=(.+)/', $token, $matches):
                return new \Symfony\Component\Console\Input\InputArgument(
                    $matches[1],
                    \Symfony\Component\Console\Input\InputArgument::OPTIONAL,
                    $description,
                    $matches[2]
                );

            default:
                return new \Symfony\Component\Console\Input\InputArgument(
                    $token,
                    \Symfony\Component\Console\Input\InputArgument::REQUIRED,
                    $description
                );
        }
    }

    protected static function parseOption(string $token): \Symfony\Component\Console\Input\InputOption
    {
        [$token, $description] = self::extractDescription($token);

        $matches = preg_split('/\s*\|\s*/', $token, 2);

        if (isset($matches[1])) {
            $shortcut = $matches[0];
            $token = $matches[1];
        } else {
            $shortcut = null;
        }

        switch (true) {
            case str_ends_with($token, '='):
                return new \Symfony\Component\Console\Input\InputOption(
                    trim($token, '='),
                    $shortcut,
                    \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL,
                    $description
                );

            case str_ends_with($token, '=*'):
                return new \Symfony\Component\Console\Input\InputOption(
                    trim($token, '=*'),
                    $shortcut,
                    \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL |
                        \Symfony\Component\Console\Input\InputOption::VALUE_IS_ARRAY,
                    $description
                );

            case preg_match('/(.+)=\*(.+)/', $token, $matches):
                return new \Symfony\Component\Console\Input\InputOption(
                    $matches[1],
                    $shortcut,
                    \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL |
                        \Symfony\Component\Console\Input\InputOption::VALUE_IS_ARRAY,
                    $description,
                    preg_split('/,\s?/', $matches[2])
                );

            case preg_match('/(.+)=(.+)/', $token, $matches):
                return new \Symfony\Component\Console\Input\InputOption(
                    $matches[1],
                    $shortcut,
                    \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL,
                    $description,
                    $matches[2]
                );

            default:
                return new \Symfony\Component\Console\Input\InputOption(
                    $token,
                    $shortcut,
                    \Symfony\Component\Console\Input\InputOption::VALUE_NONE,
                    $description
                );
        }
    }

    protected static function extractDescription(string $token): array
    {
        $parts = preg_split('/\s+:\s+/', trim($token), 2);

        if (count($parts) === 2) {
            return $parts;
        }

        return [$token, ''];
    }
}
