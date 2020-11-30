<?php

$negotiator = (new \Next\Http\RequestMethodNegotiator())
    ->get(fn () => 'get handler')
    ->post(fn () => 'post handler')
    ->patch(fn () => 'patch handler')
    ->put(fn () => 'put handler')
    ->delete(fn () => 'delete handler')
    ->fallback(fn () => 'fallback handler');

test('RequestMethodNegotiator can define fallback handler', function () use ($negotiator) {
    $this->assertEquals('fallback handler', $negotiator->negotiate(''));
});

test('RequestMethodNegotiator can define get handler', function () use ($negotiator) {
    $this->assertEquals('get handler', $negotiator->negotiate('GET'));
});

test('RequestMethodNegotiator can define post handler', function () use ($negotiator) {
    $this->assertEquals('post handler', $negotiator->negotiate('POST'));
});

test('RequestMethodNegotiator can define put handler', function () use ($negotiator) {
    $this->assertEquals('put handler', $negotiator->negotiate('PUT'));
});

test('RequestMethodNegotiator can define patch handler', function () use ($negotiator) {
    $this->assertEquals('patch handler', $negotiator->negotiate('PATCH'));
});

test('RequestMethodNegotiator can define delete handler', function () use ($negotiator) {
    $this->assertEquals('delete handler', $negotiator->negotiate('DELETE'));
});

test('RequestMethodNegotiator will infer OPTIONS by default', function () {
    $negotiator = (new \Next\Http\RequestMethodNegotiator())
        ->get(fn () => null)
        ->post(fn () => null);

    $response = $negotiator->negotiate('OPTIONS');

    $this->assertInstanceOf(\Next\Http\Response::class, $response);
    $this->assertEquals('GET, POST, OPTIONS', $response->headers->get('Allow'));
});

test('RequestMethodNegotiator will throw exception if method not handled', function () {
    $negotiator = new \Next\Http\RequestMethodNegotiator();

    $this->expectException(\RuntimeException::class);

    $negotiator->negotiate('GET');
});

test('RequestMethodNegotiator can be accessed via request instance', function () {
    $this->assertInstanceOf(\Next\Http\RequestMethodNegotiator::class, (new \Next\Http\Request())->when());
});
