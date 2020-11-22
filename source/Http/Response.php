<?php

namespace Next\Http;

class Response extends \Illuminate\Http\Response
{
    public function json(array $data = null, int $status = 200)
    {
        $this->headers->set('Content-Type', 'application/json');
        $this->setContent(json_encode($data));
        $this->setStatusCode($status);
        $this->send();
    }
}
