<?php

$config = require __DIR__ . '/../../demo/next.config.php';

if (isset($config['proxies'])) {
    foreach ($config['proxies'] as $alias => $class) {
        class_alias($class, $alias);
    }
}
