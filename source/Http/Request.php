<?php

namespace Next\Http;

/**
 * @method \Rakit\Validation\Validation validate(array $rules = [], array $messages = [])
 */
class Request extends \Illuminate\Http\Request
{
    /**
     * @var \Symfony\Component\HttpFoundation\ParameterBag<string, mixed>
     */
    protected $params;

    /**
     * @param array<string, mixed> $params
     *
     * @return static
     */
    public function setParams(array $params)
    {
        $this->params = new \Symfony\Component\HttpFoundation\ParameterBag($params);
        return $this;
    }

    public function getParams(): mixed
    {
        return $this->params;
    }

    public function param(string $key): mixed
    {
        return $this->params->get($key);
    }

    public function when(): \Next\Http\RequestMethodNegotiator
    {
        return new \Next\Http\RequestMethodNegotiator();
    }

    public function getPathInfoWithoutExtension(): string
    {
        $pathInfo = $this->getPathInfo();
        $pathInfoExtension = $this->getPathInfoExtension();

        if ($pathInfoExtension) {
            return substr($pathInfo, 0, strripos($pathInfo, ".{$pathInfoExtension}"));
        }

        return $pathInfo;
    }

    /**
     * @return string|null
     */
    public function getPathInfoExtension(): mixed
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
