<?php

$negotiator = (new \Next\Http\ResponseTypeNegotiator())
    ->when('html', fn () => 'html handler')
    ->fallback(fn () => 'default handler');

test('ResponseTypeNegotiator can define a default handler', function () use ($negotiator) {
    $this->assertEquals('default handler', $negotiator->negotiate(''));
});

test('ResponseTypeNegotiator can define specific extension handler', function () use ($negotiator) {
    $this->assertEquals('html handler', $negotiator->negotiate('html'));
});

test('ResponseTypeNegotiator will throw exception if extension not handled', function () {
    $negotiator = new \Next\Http\ResponseTypeNegotiator();

    $this->expectException(\RuntimeException::class);

    $negotiator->negotiate('get');
});

test('ResponseTypeNegotiator can be access via response instance', function () {
    $this->assertInstanceOf(\Next\Http\ResponseTypeNegotiator::class, (new \Next\Http\Response())->for());
});
