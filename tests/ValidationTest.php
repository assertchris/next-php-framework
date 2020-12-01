<?php

$defaults = [
    'paths' => [
        'pages' => __DIR__ . '/../pages',
    ],
    'proxies' => [
        \Next\Validation::class => \Next\Validation\Proxy::class,
    ],
];

test('validation is stored in the container', function () use ($defaults) {
    $app = new \Next\App($defaults);

    $this->assertInstanceOf(\Next\Validation\Proxy::class, $app[\Next\Validation::class]);
    $this->assertInstanceOf(\Next\Validation\Proxy::class, \Next\Validation::getInstance());
});

test('validation has a run method', function () use ($defaults) {
    $app = new \Next\App($defaults);

    $proxy = $app[\Next\Validation::class];

    $bad = $proxy->run(['foo' => null], ['foo' => 'required']);

    $this->assertInstanceOf(\Rakit\Validation\Validation::class, $bad);
    $this->assertTrue($bad->fails());
    $this->assertNotEmpty($bad->errors()->firstOfAll());

    $good = $proxy->run(['foo' => 'bar'], ['foo' => 'required']);

    $this->assertInstanceOf(\Rakit\Validation\Validation::class, $good);
    $this->assertFalse($good->fails());
    $this->assertEmpty($good->errors()->firstOfAll());
});

test('validation adds a request macro', function () use ($defaults) {
    $app = new \Next\App($defaults);

    $request = $app[\Next\Http\Request::class];

    // set this to post or the replace will replace querystring parameters
    $request->setMethod('post');

    // replace existing input parameters with these
    $request->replace(['foo' => 'bar']);

    $validation = $request->validate(['foo' => 'required']);

    $this->assertInstanceOf(\Rakit\Validation\Validation::class, $validation);
    $this->assertFalse($validation->fails());
});
