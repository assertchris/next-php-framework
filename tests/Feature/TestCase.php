<?php

namespace Tests\Feature;

class TestCase extends \PHPUnit\Framework\TestCase
{
    protected \Next\App $app;

    protected function setUp(): void
    {
        $this->app = new \Next\App([
            'paths' => [
                'pages' => __DIR__ . '/pages',
            ],
        ]);
    }

    private function handle(string $method, string $uri, array $parameters = []): \Next\Testing\TestResponse
    {
        $request = \Next\Http\Request::create($uri, $method, $parameters);

        return \Next\Testing\TestResponse::fromBaseResponse($this->app->serve($request));
    }

    public function get(string $uri, array $parameters = []): \Next\Testing\TestResponse
    {
        return $this->handle('GET', $uri, $parameters);
    }

    public function post(string $uri, array $parameters = []): \Next\Testing\TestResponse
    {
        return $this->handle('POST', $uri, $parameters);
    }

    public function put(string $uri, array $parameters = []): \Next\Testing\TestResponse
    {
        return $this->handle('PUT', $uri, $parameters);
    }

    public function delete(string $uri): \Next\Testing\TestResponse
    {
        return $this->handle('DELETE', $uri);
    }

    public function options(string $uri): \Next\Testing\TestResponse
    {
        return $this->handle('OPTIONS', $uri);
    }
}
