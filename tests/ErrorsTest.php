<?php

$defaults = [
    'paths' => [
        'pages' => __DIR__ . '/../pages',
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

    $this->assertInstanceOf(\Next\Errors\SafeErrorHtmlHandler::class, $handler);
});

$types = ['500' => 'Something went wrong.', '405' => 'Method not allowed.', '404' => 'Not found.'];

foreach (array_keys($types) as $code) {
    test("errors can show a safe JSON {$code} error", function () use ($defaults, $code) {
        $app = new \Next\App(array_merge($defaults));

        $handler = new \Next\Errors\SafeErrorJsonHandler();
        $handler(new \Exception($code));

        $response = $app[\Next\Http\Response::class];

        $this->assertEquals('{"status":"error","code":' . $code . '}', $response->getContent());
    });
}

foreach ($types as $code => $message) {
    test("errors can show custom safe HTML {$code} error", function () use ($defaults, $code, $message) {
        $app = new \Next\App(array_merge($defaults));

        ob_start();
        $handler = new \Next\Errors\SafeErrorHtmlHandler();
        $handler(new \Exception($code));
        $content = ob_get_contents();
        ob_end_clean();

        $this->assertEquals($message, $content);
    });
}

foreach ($types as $code => $message) {
    test("errors can show framework safe HTML {$code} error", function () use ($defaults, $code, $message) {
        $app = new \Next\App(array_merge($defaults, ['paths' => ['pages' => 'missing']]));

        ob_start();
        $handler = new \Next\Errors\SafeErrorHtmlHandler();
        $handler(new \Exception($code));
        $content = ob_get_contents();
        ob_end_clean();

        $this->assertEquals($message, $content);
    });
}
