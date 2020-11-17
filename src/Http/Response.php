<?php

namespace Next\Http;

use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class Response extends SymfonyResponse
{
    public function status(int $code): static
    {
        $this->setStatus($code);
        return $this;
    }

    public function json(array $data = null)
    {
        $this->headers->set('Content-Type', 'application/json');
        $this->setContent(json_encode($data));
        $this->send();
    }
}
