<?php

beforeAll(fn () => static::$pagesPath = __DIR__ . '/responseTypesPages');

it('can return string content')
    ->get('/string-response')
    ->assertSee('String content');
