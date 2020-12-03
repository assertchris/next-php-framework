<?php

namespace Next\Http;

class Response extends \Illuminate\Http\Response
{
    /**
     * @param array<mixed> $params
     * @return \Next\Http\JsonResponse
     */
    public function json(...$params): \Next\Http\JsonResponse
    {
        $response = new \Next\Http\JsonResponse(...$params);

        \Next\App::getInstance()->instance(static::class, $response);

        return $response;
    }

    public function redirect(string $url): \Next\Http\RedirectResponse
    {
        $response = new \Next\Http\RedirectResponse($url);

        \Next\App::getInstance()->instance(static::class, $response);

        return $response;
    }

    public function for(): \Next\Http\ResponseTypeNegotiator
    {
        return new \Next\Http\ResponseTypeNegotiator();
    }
}
