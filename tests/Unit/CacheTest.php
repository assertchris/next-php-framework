<?php

$defaults = [
    'paths' => [
        'pages' => __DIR__ . '/../pages',
    ],
    'proxies' => [
        \Next\Cache::class => \Next\Cache\Proxy::class,
    ],
];

test('cache is stored in the container', function () use ($defaults) {
    $app = new \Next\App($defaults);

    $this->assertInstanceOf(\Next\Cache\Proxy::class, $app[\Next\Cache::class]);
    $this->assertInstanceOf(\Next\Cache\Proxy::class, \Next\Cache::getInstance());
});

test('cache can put and get', function () use ($defaults) {
    $app = new \Next\App($defaults);

    $cache = $app[\Next\Cache::class];
    $cache->put('i-can-put-and-get', 'i can put and get', 1); // ← has ttl

    $this->assertEquals($cache->get('i-can-put-and-get'), 'i can put and get');
    sleep(1);
    $this->assertEquals($cache->get('i-can-put-and-get'), null);
});

test('cache can put and get forever', function () use ($defaults) {
    $app = new \Next\App($defaults);

    $cache = $app[\Next\Cache::class];
    $cache->put('i-can-put-and-get-forever', 'i can put and get forever'); // ← has no ttl

    $this->assertEquals($cache->get('i-can-put-and-get-forever'), 'i can put and get forever');
});

test('cache can have', function () use ($defaults) {
    $app = new \Next\App($defaults);

    $cache = $app[\Next\Cache::class];
    $cache->put('i-can-have', 'i-can-have');

    $this->assertTrue($cache->has('i-can-have'));
});

test('cache can remember', function () use ($defaults) {
    $app = new \Next\App($defaults);

    $cache = $app[\Next\Cache::class];

    $result = $cache->remember('i-can-remember', fn() => 'i can remember', 1); // ← has ttl

    $this->assertEquals($result, 'i can remember');
    $this->assertEquals($cache->get('i-can-remember'), 'i can remember');
    sleep(1);
    $this->assertEquals($cache->get('i-can-remember'), null);
});

test('cache remember only calls closure once', function () use ($defaults) {
    $app = new \Next\App($defaults);

    $cache = $app[\Next\Cache::class];

    $closure = $this
        ->getMockBuilder(\stdClass::class)
        ->addMethods(['__invoke'])
        ->getMock();

    $closure
        ->expects($this->once())
        ->method('__invoke')
        ->willReturn('i can remember');

    $cache->remember('i-only-get-called-once', $closure, 1);

    $this->assertEquals('i can remember', $cache->remember('i-only-get-called-once', $closure, 1));
    $this->assertEquals('i can remember', $cache->remember('i-only-get-called-once', $closure, 1));
});

test('cache can remember forever', function () use ($defaults) {
    $app = new \Next\App($defaults);

    $cache = $app[\Next\Cache::class];

    $result = $cache->remember('i-can-remember-forever', fn() => 'i can remember forever'); // ← has no ttl

    $this->assertEquals($result, 'i can remember forever');
    $this->assertEquals($cache->get('i-can-remember-forever'), 'i can remember forever');
});

test('cache can forget', function () use ($defaults) {
    $app = new \Next\App($defaults);

    $cache = $app[\Next\Cache::class];
    $cache->put('i-can-forget', 'i can forget');
    $cache->forget('i-can-forget');

    $this->assertEquals($cache->get('i-can-forget'), null);
});

test('cache can flush', function () use ($defaults) {
    $app = new \Next\App($defaults);

    $cache = $app[\Next\Cache::class];
    $cache->put('i-can-flush', 'i can flush');
    $cache->flush();

    $this->assertEquals($cache->get('i-can-forget'), null);
});

test('cache can add', function () use ($defaults) {
    $app = new \Next\App($defaults);

    $cache = $app[\Next\Cache::class];
    $cache->add('i-can-add', 'i can add');

    $this->assertEquals($cache->get('i-can-add'), 'i can add');

    $cache->add('i-can-add', 'a different value');

    $this->assertEquals($cache->get('i-can-add'), 'i can add');
});

test('cache can default', function () use ($defaults) {
    $app = new \Next\App($defaults);

    $cache = $app[\Next\Cache::class];

    $this->assertEquals($cache->get('i-can-default', 123), 123);
    $this->assertEquals($cache->get('i-can-default', fn() => 123), 123);
});
