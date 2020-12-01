<?php

namespace Next\Testing;

/**
 * Based on Laravel 8's test response.
 *
 * @mixin \Next\Http\Response
 */
class TestResponse
{
    private \Next\Http\Response $baseResponse;

    public function __construct(\Next\Http\Response $response)
    {
        $this->baseResponse = $response;
    }

    public static function fromBaseResponse(\Next\Http\Response $response): static
    {
        return new static($response);
    }

    public function assertSee(string|array $value, bool $escape = true): self
    {
        $value = is_array($value) ? $value : [$value];

        $values = $escape ? array_map('e', ($value)) : $value;

        foreach ($values as $value) {
            \PHPUnit\Framework\Assert::assertStringContainsString((string) $value, $this->getContent());
        }

        return $this;
    }

    public function assertJson(array $data, bool $strict = false): self
    {
        \PHPUnit\Framework\Assert::assertEquals($data, json_decode($this->getContent()));

        return $this;
    }

    public function assertHeader(string $headerName, mixed $value = null): self
    {
        \PHPUnit\Framework\Assert::assertTrue($this->headers->has($headerName), "Header [{$headerName}] not present on response.");

        $actual = $this->headers->get($headerName);

        if (!is_null($value)) {
            \PHPUnit\Framework\Assert::assertEquals($value, $this->headers->get($headerName), "Header [{$headerName}] was found, but value [{$actual}] does not match [{$value}].");
        }

        return $this;
    }

    public function __get(string $key): mixed
    {
        return $this->baseResponse->{$key};
    }

    public function __isset(string $key): bool
    {
        return isset($this->baseResponse->{$key});
    }

    public function __call(string $method, array $args): mixed
    {
        return $this->baseResponse->{$method}(... $args);
    }
}
