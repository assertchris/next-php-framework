<?php

namespace Next\Http;

class JsonResponse extends \Illuminate\Http\JsonResponse
{
    public function json(): static
    {
        return $this;
    }
}
