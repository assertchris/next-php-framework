<?php

test('cache is stored in the container', function() {
    $app = new \Next\App([
        'paths' => [
            'pages' => __DIR__ . '/../pages',
        ],
        'proxies' => [
            \Next\Cache::class => \Next\Cache\Proxy::class,
        ],
    ]);

    $this->assertInstanceOf(\Next\Cache\Proxy::class, $app[\Next\Cache::class]);
    $this->assertInstanceOf(\Next\Cache\Proxy::class, \Next\Cache::getInstance());
});
