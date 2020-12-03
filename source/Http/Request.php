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
    protected \Symfony\Component\HttpFoundation\ParameterBag $params;

    /**
     * @param array<string, mixed> $params
     *
     * @return static
     */
    public function setParams(array $params): static
    {
        $this->params = new \Symfony\Component\HttpFoundation\ParameterBag($params);
        return $this;
    }

    public function getParams(): \Symfony\Component\HttpFoundation\ParameterBag
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
    public function getPathInfoExtension(): ?string
    {
        return strtolower(pathinfo(parse_url($this->url(), PHP_URL_PATH), PATHINFO_EXTENSION));
    }
}
