<?php

namespace Next\Http;

/**
 * @method \Rakit\Validation\Validation validate(array $rules = [], array $messages = [])
 */
class Request extends \Illuminate\Http\Request
{
    protected $params;

    public function setParams(array $params)
    {
        $this->params = new \Symfony\Component\HttpFoundation\ParameterBag($params);
    }

    public function getParams()
    {
        return $this->params;
    }

    public function param(string $key)
    {
        return $this->params->get($key);
    }

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
