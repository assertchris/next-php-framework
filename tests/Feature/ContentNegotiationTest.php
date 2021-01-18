<?php

beforeEach(fn () => $this->withPages([
    'api' => [
        'nomadic-content-negotiation.php' => "
            <?php
            return function () {
                return request()
                    ->when()
                    ->get(fn() => response()
                        ->for()
                        ->html(fn() => '<h1>HTML Content</h1>')
                        ->json(fn() => json_encode([
                            'hello' => 'world',
                       ]))
                    )
                    ->post(fn() => 'POST handler');
            };
        ",
        'nomadic-content-negotiation-with-defaults.php' => "
            <?php
            return function () {
                return request()
                    ->when()
                    ->get(fn () => response()->for()->default(fn () => 'default content'))
                    ->default(fn () => 'default handler');
            };
        ",
    ],
]));

it('returns HTML content when using .html extension')
    ->get('/api/nomadic-content-negotiation.html')
    ->assertSee('<h1>HTML Content</h1>', false);

it('returns JSON content when using .json extension')
    ->get('/api/nomadic-content-negotiation.json')
    ->assertSee('{"hello":"world"}', false);

it('calls POST handler')
    ->post('/api/nomadic-content-negotiation')
    ->assertSee('POST handler');

// TODO: we should get a 405 error instead.
it('throws exception by default when method not negotiated')
    ->expectException(\RuntimeException::class)
    ->delete('/api/nomadic-content-negotiation');

// TODO: we should get a 415 error instead.
it('throws exception by default when content type is not negotiated')
    ->expectException(\RuntimeException::class)
    ->get('/api/nomadic-content-negotiation.pdf');

it('calls default handler when method not negotiated')
    ->post('/api/nomadic-content-negotiation-with-defaults')
    ->assertSee('default handler');

it('calls default handler when content type not negotiated')
    ->get('/api/nomadic-content-negotiation-with-defaults')
    ->assertSee('default content');

it('will infer OPTIONS response by default')
    ->options('/api/nomadic-content-negotiation')
    ->assertHeader('Allow', 'GET, POST, OPTIONS');