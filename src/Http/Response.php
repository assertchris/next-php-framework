<?php

namespace Next\Http;

use Illuminate\Http\Response as IlluminateResponse;

class Response extends IlluminateResponse
{
    public function json(array $data = null, ?int $status = 200)
    {
        $this->headers->set('Content-Type', 'application/json');
        $this->setContent(json_encode($data));
        $this->setStatusCode($status);
        $this->send();
    }
}
