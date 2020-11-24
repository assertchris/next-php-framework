<?php

namespace Next\Http;

class Response extends \Illuminate\Http\Response
{
    public function json(...$params)
    {
        return new \Next\Http\JsonResponse(...$params);
    }

    public function redirect(...$params)
    {
        return new \Next\Http\RedirectResponse(...$params);
    }

    public function for()
    {
        return new \Next\Http\ResponseTypeNegotiator();
    }
}
