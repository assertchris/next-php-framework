<?php

it('can return string content')
    ->get('/string-response')
    ->assertSee('String content');
