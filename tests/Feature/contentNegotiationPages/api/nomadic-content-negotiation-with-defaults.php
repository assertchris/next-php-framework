<?php

return function () {
    return request()
        ->when()
        ->get(fn () => response()->for()->default(fn () => 'default content'))
        ->default(fn () => 'default handler');
};