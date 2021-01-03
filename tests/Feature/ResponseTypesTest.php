<?php

it('can return string content')
    ->withPages([
        'string-response.php' => "<?php return fn () => 'String content.';",
    ])
    ->get('/string-response')
    ->assertSee('String content');
