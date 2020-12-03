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
        /**
         * @phpstan-ignore-next-line
         */
        return new static($response);
    }

    /**
     * @param string|array<string> $value
     * @param bool                 $escape
     * @return static
     */
    public function assertSee(mixed $value, bool $escape = true): static
    {
        $value = is_array($value) ? $value : [$value];

        $values = $escape ? array_map('e', $value) : $value;

        foreach ($values as $value) {
            \PHPUnit\Framework\Assert::assertStringContainsString((string) $value, $this->getContent());
        }

        return $this;
    }

    /**
     * @param array $data
     * @param bool  $strict
     * @return static
     */
    public function assertJson(array $data, bool $strict = false): static
    {
        \PHPUnit\Framework\Assert::assertEquals($data, json_decode($this->getContent()));

        return $this;
    }

    /**
     * @param string     $headerName
     * @param mixed|null $value
     * @return static
     */
    public function assertHeader(string $headerName, mixed $value = null): static
    {
        \PHPUnit\Framework\Assert::assertTrue(
            $this->headers->has($headerName),
            "Header [{$headerName}] not present on response."
        );

        $actual = $this->headers->get($headerName);

        if (!is_null($value)) {
            \PHPUnit\Framework\Assert::assertEquals(
                $value,
                $this->headers->get($headerName),
                "Header [{$headerName}] was found, but value [{$actual}] does not match [{$value}]."
            );
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

    /**
     * @param string $method
     * @param array  $params
     * @return mixed
     */
    public function __call(string $method, array $params): mixed
    {
        return $this->baseResponse->{$method}(...$params);
    }
}
