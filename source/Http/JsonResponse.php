<?php

namespace Next\Http;

class JsonResponse extends \Illuminate\Http\JsonResponse
{
    /**
     * @return static
     */
    public function json()
    {
        return $this;
    }
}
