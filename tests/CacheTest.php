<?php

$defaults = [
    'paths' => [
        'pages' => __DIR__ . '/../pages',
    ],
    'proxies' => [
        \Next\Cache::class => \Next\Cache\Proxy::class,
    ],
];

$app = new \Next\App($defaults);

test('cache is stored in the container', function () use ($app) {
    $this->assertInstanceOf(\Next\Cache\Proxy::class, $app[\Next\Cache::class]);
    $this->assertInstanceOf(\Next\Cache\Proxy::class, \Next\Cache::getInstance());
});

test('cache can put and get', function () use ($app) {
    $cache = $app[\Next\Cache::class];
    $cache->put('i-can-put-and-get', 'i can put and get', 1); // ← has ttl

    $this->assertEquals($cache->get('i-can-put-and-get'), 'i can put and get');
    sleep(1);
    $this->assertEquals($cache->get('i-can-put-and-get'), null);
});

test('cache can put and get forever', function () use ($app) {
    $cache = $app[\Next\Cache::class];
    $cache->put('i-can-put-and-get-forever', 'i can put and get forever'); // ← has no ttl

    $this->assertEquals($cache->get('i-can-put-and-get-forever'), 'i can put and get forever');
});

test('cache can have', function () use ($app) {
    $cache = $app[\Next\Cache::class];
    $cache->put('i-can-have', 'i-can-have');

    $this->assertTrue($cache->has('i-can-have'));
});

test('cache can remember', function () use ($app) {
    $cache = $app[\Next\Cache::class];

    $cache->remember('i-can-remember', function () {
        return 'i can remember';
    }, 1); // ← has ttl

    $this->assertEquals($cache->get('i-can-remember'), 'i can remember');
    sleep(1);
    $this->assertEquals($cache->get('i-can-remember'), null);
});

test('cache can remember forever', function () use ($app) {
    $cache = $app[\Next\Cache::class];

    $result = $cache->remember('i-can-remember-forever', function () {
        return 'i can remember forever';
    }); // ← has no ttl

    $this->assertEquals($cache->get('i-can-remember-forever'), 'i can remember forever');
});

test('cache can forget', function () use ($app) {
    $cache = $app[\Next\Cache::class];
    $cache->put('i-can-forget', 'i can forget');
    $cache->forget('i-can-forget');

    $this->assertEquals($cache->get('i-can-forget'), null);
});

test('cache can flush', function () use ($app) {
    $cache = $app[\Next\Cache::class];
    $cache->put('i-can-flush', 'i can flush');
    $cache->flush();

    $this->assertEquals($cache->get('i-can-forget'), null);
});

test('cache can add', function () use ($app) {
    $cache = $app[\Next\Cache::class];
    $cache->add('i-can-add', 'i can add');

    $this->assertEquals($cache->get('i-can-add'), 'i can add');

    $cache->add('i-can-add', 'a different value');

    $this->assertEquals($cache->get('i-can-add'), 'i can add');
});

test('cache can default', function () use ($app) {
    $cache = $app[\Next\Cache::class];

    $this->assertEquals($cache->get('i-can-default', 123), 123);
    $this->assertEquals($cache->get('i-can-default', fn() => 123), 123);
});