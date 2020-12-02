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
