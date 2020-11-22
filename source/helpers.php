<?php

use Next\App;

if (!function_exists('dd')) {
    function dd(...$params)
    {
        var_dump(...$params);
        die();
    }
}

if (!function_exists('app')) {
    function app($abstract = null, array $parameters = [])
    {
        if (is_null($abstract)) {
            return App::getInstance();
        }

        return App::getInstance()->make($abstract, $parameters);
    }
}

if (!function_exists('path')) {
    function path(string $key): ?string
    {
        return app("path.{$key}");
    }
}

if (!function_exists('files')) {
    function files($folder, $extension = 'php'): array
    {
        $directory = new RecursiveDirectoryIterator($folder);
        $iterator = new RecursiveIteratorIterator($directory);

        $files = [];

        foreach($iterator as $file) {
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
