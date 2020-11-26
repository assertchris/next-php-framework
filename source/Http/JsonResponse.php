<?php

namespace Next\Http;

class JsonResponse extends \Illuminate\Http\JsonResponse
{
    public function json(...$params)
    {
        return $this;
    }
}
