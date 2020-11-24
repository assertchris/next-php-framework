<?php

namespace Next\Http;

class Request extends \Illuminate\Http\Request
{
    public function when()
    {
        return new \Next\Http\RequestMethodNegotiator();
    }

    public function getPathInfoWithoutExtension()
    {
        $pathInfo = $this->getPathInfo();
        $pathInfoExtension = $this->getPathInfoExtension();

        if ($pathInfoExtension) {
            return substr($pathInfo, 0, strripos($pathInfo, ".{$pathInfoExtension}"));
        }

        return $pathInfo;
    }

    public function getPathInfoExtension(): ?string
    {
        $pathInfo = $this->getPathInfo();

        foreach (\Next\Http\ResponseTypeNegotiator::TYPES as $type) {
            if (str_ends_with($pathInfo, ".{$type}")) {
                return $type;
            }
        }

        return null;
    }
}
