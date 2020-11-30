<?php

test('request can get uri path without file extension', function () {
    $request = \Next\Http\Request::create('/invoices/1001.pdf');

    $this->assertEquals('/invoices/1001', $request->getPathInfoWithoutExtension());
});

test('request can read file extension', function () {
    $request = \Next\Http\Request::create('/example.html');

    $this->assertEquals('html', $request->getPathInfoExtension());
});
