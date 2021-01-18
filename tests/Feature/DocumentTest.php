<?php

beforeEach(fn () => $this->withPages([
    '_document.php' => '<?php return fn ($content) => "Document content; " . $content;',
    'string-response.php' => '<?php return fn () => "String content";',
]));

it('wraps string content')
    ->get('/string-response')
    ->assertSee('Document content; String content');
