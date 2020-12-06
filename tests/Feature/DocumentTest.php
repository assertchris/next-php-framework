<?php

beforeAll(fn () => static::$pagesPath = __DIR__ . '/documentTestPages');

it('wraps string content')
    ->get('/string-response')
    ->assertSee('Document content; String content');
