<?php

$defaults = [
    'paths' => [
        'pages' => __DIR__,
    ],
    'proxies' => [
        \Next\Errors::class => \Next\Errors\Proxy::class,
    ],
];

test('errors is stored in the container', function () use ($defaults) {
    $app = new \Next\App($defaults);

    $this->assertInstanceOf(\Next\Errors\Proxy::class, $app[\Next\Errors::class]);
    $this->assertInstanceOf(\Next\Errors\Proxy::class, \Next\Errors::getInstance());
});

test('errors can enable JSON handler', function () use ($defaults) {
    $app = new \Next\App(array_merge($defaults, ['env' => 'dev']));

    $errors = $app[\Next\Errors::class];
    $errors->enableJsonHandler($app);

    $connection = Tests\valueOf($errors, 'connection');
    $handlerStack = Tests\valueOf($connection, 'handlerStack');

    $this->assertInstanceOf(\Whoops\Handler\JsonResponseHandler::class, $handlerStack[0]);
});

test('errors can enable HTML handler', function () use ($defaults) {
    $app = new \Next\App(array_merge($defaults, ['env' => 'dev']));

    $errors = $app[\Next\Errors::class];
    $errors->enableHtmlHandler($app);

    $connection = Tests\valueOf($errors, 'connection');
    $handlerStack = Tests\valueOf($connection, 'handlerStack');

    $this->assertInstanceOf(\Whoops\Handler\PrettyPageHandler::class, $handlerStack[0]);
});

test('errors can enable safe JSON handler', function () use ($defaults) {
    $app = new \Next\App(array_merge($defaults, ['env' => 'prod']));

    $errors = $app[\Next\Errors::class];
    $errors->enableJsonHandler($app);

    $connection = Tests\valueOf($errors, 'connection');
    $handlerStack = Tests\valueOf($connection, 'handlerStack');

    $this->assertInstanceOf(\Whoops\Handler\CallbackHandler::class, $handlerStack[0]);

    $handler = Tests\valueOf($handlerStack[0], 'callable');

    $this->assertInstanceOf(\Next\Errors\SafeErrorJsonHandler::class, $handler);
});

test('errors can enable safe HTML handler', function () use ($defaults) {
    $app = new \Next\App(array_merge($defaults, ['env' => 'prod']));

    $errors = $app[\Next\Errors::class];
    $errors->enableHtmlHandler($app);

    $connection = Tests\valueOf($errors, 'connection');
    $handlerStack = Tests\valueOf($connection, 'handlerStack');

    $this->assertInstanceOf(\Whoops\Handler\CallbackHandler::class, $handlerStack[0]);

    $handler = Tests\valueOf($handlerStack[0], 'callable');

    $this->assertInstanceOf(\Next\Errors\SafeErrorPageHandler::class, $handler);
});
