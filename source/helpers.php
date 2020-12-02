<?php

use Next\App;

if (!function_exists('app')) {
    /**
     * @phpstan-ignore-next-line
     */
    function app(string $abstract = null, array $parameters = []): mixed
    {
        if (is_null($abstract)) {
            return App::getInstance();
        }

        return App::getInstance()->make($abstract, $parameters);
    }
}

if (!function_exists('request')) {
    function request(): \Next\Http\Request
    {
        return app(\Next\Http\Request::class);
    }
}

if (!function_exists('response')) {
    /**
     * @return \Next\Http\Response|\Next\Http\JsonResponse|\Next\Http\RedirectResponse
     */
    function response(string $content = null): mixed
    {
        if ($content) {
            return \Next\Http\Response::create($content);
        }

        return app(\Next\Http\Response::class);
    }
}

if (!function_exists('path')) {
    function path(string $key): ?string
    {
        return app("path.{$key}");
    }
}

if (!function_exists('files')) {
    /**
     * @return array<string>
     */
    function files(string $folder, string $extension = 'php'): array
    {
        $directory = new RecursiveDirectoryIterator($folder);
        $iterator = new RecursiveIteratorIterator($directory);

        $files = [];

        foreach ($iterator as $file) {
            if (!is_file($file->getPathname())) {
                continue;
            }

            if (!str_ends_with($file->getFilename(), $extension)) {
                continue;
            }

            $files[] = $file->getPathname();
        }

        return $files;
    }
}
