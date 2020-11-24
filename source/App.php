<?php

namespace Next;

class App extends \Illuminate\Container\Container
{
    public function __construct(array $config = [])
    {
        static::setInstance($this);

        $this['config'] = $config;

        if (!isset($config['paths'])) {
            throw new \InvalidArgumentException('paths not defined');
        }

        $this->configurePaths($config['paths']);
        $this->configureProxies();
    }

    private function configurePaths(array $paths = [])
    {
        if (!isset($paths['pages'])) {
            throw new \InvalidArgumentException('paths.pages not defined');
        }

        foreach ($paths as $key => $path) {
            $this->instance("path.{$key}", $path);
        }
    }

    private function configureProxies()
    {
        if (isset($this['config']['proxies'])) {
            foreach ($this['config']['proxies'] as $alias => $class) {
                if (!class_exists($class)) {
                    throw new \InvalidArgumentException("{$class} not defined");
                }

                $class::connect($this);

                if ($class !== $alias) {
                    class_alias($class, $alias);
                }

                $this->instance($alias, $class::getInstance());
            }
        }
    }

    public function serve()
    {
        $request = \Next\Http\Request::capture();
        $response = \Next\Http\Response::create();

        if ($this->has(\Next\Errors::class)) {
            $errors = $this[\Next\Errors::class];

            if ($request->expectsJson()) {
                $errors->enableJsonHandler($this);
            } else {
                $errors->enableHtmlHandler($this);
            }
        }

        $this->instance(\Next\Http\Request::class, $request);
        $this->instance(\Next\Http\Response::class, $response);

        if (isset($this['config']['middleware'])) {
            foreach ($this['config']['middleware'] as $middleware) {
                $class = new $middleware();
                $class->handle($this, $request, $response);
            }
        }

        $this->route($request);
    }

    private function route(\Next\Http\Request $request)
    {
        $path = path('pages');
        $allFiles = files($path);
        $apiFiles = files("{$path}/api");
        $pageFiles = array_diff($allFiles, $apiFiles);

        $dispatcher = \FastRoute\simpleDispatcher(function (\FastRoute\RouteCollector $collector) use ($path, $apiFiles, $pageFiles) {
            foreach ($apiFiles as $apiFile) {
                if (!is_file($apiFile)) {
                    continue;
                }

                if (str_starts_with($apiFile, "_")) {
                    continue;
                }

                $apiFilePath = str_replace($path, '', dirname($apiFile));
                $apiFileName = basename($apiFile, '.php');

                if ($apiFileName === 'index') {
                    $apiFileName = '';
                }

                $collector->addRoute('*', "{$apiFilePath}/{$apiFileName}", ['type' => 'api', 'factory' => require $apiFile]);
            }


            foreach ($pageFiles as $pageFile) {
                if (!is_file($pageFile)) {
                    continue;
                }

                if (str_starts_with($pageFile, "_")) {
                    continue;
                }

                $pageFilePath = str_replace($path, '', dirname($pageFile));
                $pageFileName = basename($pageFile, '.php');

                if ($pageFileName === 'index') {
                    $pageFileName = '';
                }

                $collector->addRoute('GET', "{$pageFilePath}/{$pageFileName}", ['type' => 'page', 'factory' => require $pageFile]);
            }
        });

        $httpMethod = $request->getMethod();
        $httpPath = $request->getBaseUrl() . $request->getPathInfoWithoutExtension();

        $routed = $dispatcher->dispatch($httpMethod, $httpPath);

        switch ($routed[0]) {
            case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                throw new \RuntimeException('405');
                break;

            case \FastRoute\Dispatcher::FOUND:
                $request->setParams($routed[2]);

                if ($routed[1]['type'] === 'api') {
                    $content = $routed[1]['factory']($request);
                    $this->dispatchResponse($content);
                }

                if ($routed[1]['type'] === 'page') {
                    $content = $routed[1]['factory']($request);

                    if (is_file("{$path}/_document.php")) {
                        $document = require "{$path}/_document.php";
                        $content = $document($request, $this->unwrapResponse($content));
                    }

                    $this->dispatchResponse($content);
                }

                break;
            
            default:
                throw new \RuntimeException('404');
                break;
        }
    }

    private function dispatchResponse(mixed $response): void
    {
        $response = $this->negotiateResponse($response);

        if (is_string($response)) {
            $response = \Next\Http\Response::create($response);
        }

        if (method_exists($response, 'send')) {
            $response->send();
        }
    }

    private function negotiateResponse(mixed $response): mixed
    {
        if ($response instanceof \Next\Http\RequestMethodNegotiator) {
            return $this->negotiateResponse($response->negotiate());
        }
        if ($response instanceof \Next\Http\ResponseTypeNegotiator) {
            return $this->negotiateResponse($response->negotiate());
        }

        return $response;
    }

    private function unwrapResponse(mixed $response): mixed
    {
        $response = $this->negotiateResponse($response);

        if ($response instanceof \Symfony\Component\HttpFoundation\Response) {
            $response = $response->getContent();
        }

        return $response;
    }
}
