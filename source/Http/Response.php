<?php

namespace Next\Http;

class Response extends \Illuminate\Http\Response
{
    public function json(...$params)
    {
        $response = new \Next\Http\JsonResponse(...$params);

        \Next\App::getInstance()->instance(static::class, $response);

        return $response;
    }

    public function redirect(...$params)
    {
        $response = new \Next\Http\RedirectResponse(...$params);

        \Next\App::getInstance()->instance(static::class, $response);

        return $response;
    }

    public function for()
    {
        return new \Next\Http\ResponseTypeNegotiator();
    }
}
