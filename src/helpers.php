<?php

use Next\App;

if (!function_exists('dd')) {
    function dd(...$params) {
        var_dump(...$params);
        die();
    }
}

if (!function_exists('app')) {
    function app(string $key = null) {
        if (is_null($key)) {
            return App::instance();
        }
        
        return App::instance()->resolve($key);
    }
}

if (!function_exists('path')) {
    function path(string $key): ?string {
        return app("path.{$key}");
    }
}

if (!function_exists('search')) {
    function files($pattern, $flags = 0): array {
        $files = glob($pattern, $flags);

        foreach (glob(dirname($pattern) . '/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir) {
            $files = array_merge($files, files($dir . '/' . basename($pattern), $flags));
        }

        return $files;
    }   
}
