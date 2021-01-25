<?php

test('A missing route results in a 404 response')
    ->get('/missing-route')
    ->assertStatus(404)
    ->assertSee('Not found.');

test('The incorrect METHOD results in a 405 response')
    ->withPages([
        'page.php' => '<?php return fn() => "Never executed";',
    ])
    ->post('/page')
    ->assertStatus(405)
    ->assertSee('Method not allowed.');

test('An exception thrown results in a 500 response')
    ->withPages([
        'error.php' => '<?php return fn () => throw new \RuntimeException();'
    ])
    ->get('/error')
    ->assertStatus(500)
    ->assertSee('Something went wrong.', false);

test('The default 404 error page can be overwritten')
    ->withPages([
        '_404.php' => '<?php return fn () => \Next\Http\Response::create("This is not the page you\'re looking for!", 404);',
    ])
    ->get('/missing-route')
    ->assertStatus(404)
    ->assertSee('This is not the page you\'re looking for!', false);

test('The default 405 error page can be overwritten')
    ->withPages([
        '_405.php' => '<?php return fn () => \Next\Http\Response::create("This is not the page you\'re looking for!", 405);',
        'post.php' => '<?php return fn () => request()->when()->post(fn () => "Hello");'
    ])
    ->get('/post')
    ->assertStatus(405)
    ->assertSee('This is not the page you\'re looking for!', false);

test('The default 415 error page can be overwritten')
    ->withPages([
        '_415.php' => '<?php return fn () => \Next\Http\Response::create("This is not the page you\'re looking for!", 415);',
        'post.php' => '<?php return fn () => response()->for()->json(fn () => response()->json(["hello"]));'
    ])
    ->get('/post')
    ->assertStatus(415)
    ->assertSee('This is not the page you\'re looking for!', false);

test('The default 500 error page can be overwritten')
    ->withPages([
        '_500.php' => '<?php return fn () => \Next\Http\Response::create("Something went really wrong!", 500);',
        'error.php' => '<?php return fn () => throw new \RuntimeException();'
    ])
    ->get('/error')
    ->assertStatus(500)
    ->assertSee('Something went really wrong!', false);
