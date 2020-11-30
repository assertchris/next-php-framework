<?php

namespace Next;

class App extends \Illuminate\Container\Container
{
    /**
     * @param array<string, mixed> $config
     */
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

    /**
     * @param array<string, string> $paths
     */
    private function configurePaths(array $paths = []): void
    {
        if (!isset($paths['pages'])) {
            throw new \InvalidArgumentException('paths.pages not defined');
        }

        foreach ($paths as $key => $path) {
            $this->instance("path.{$key}", $path);
        }

        $this->instance('path.framework', __DIR__ . '/../');
    }

    private function configureProxies(): void
    {
        if (isset($this['config']['proxies'])) {
            foreach ($this['config']['proxies'] as $alias => $class) {
                if (!class_exists($class)) {
                    throw new \InvalidArgumentException("{$class} not defined");
                }

                $class::connect($this);

                if (!class_exists($alias) && $class !== $alias) {
                    class_alias($class, $alias);
                }

                $this->instance($alias, $class::getInstance());
            }
        }
    }

    public function serve(): void
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

        $this->route($request);
    }

    private function route(\Next\Http\Request $request): void
    {
        $path = path('pages');
        $allFiles = files($path);
        $apiFiles = files("{$path}/api");
        $pageFiles = array_diff($allFiles, $apiFiles);

        $dispatcher = \FastRoute\simpleDispatcher(function (\FastRoute\RouteCollector $collector) use (
            $path,
            $apiFiles,
            $pageFiles
        ) {
            foreach ($apiFiles as $apiFile) {
                if (!is_file($apiFile)) {
                    continue;
                }

                if (str_starts_with($apiFile, '_')) {
                    continue;
                }

                $apiFilePath = str_replace($path, '', dirname($apiFile));
                $apiFileName = basename($apiFile, '.php');

                if ($apiFileName === 'index') {
                    $apiFileName = '';
                }

                $collector->addRoute('*', "{$apiFilePath}/{$apiFileName}", [
                    'type' => 'api',
                    'factory' => fn() => $this->applyMiddleware(request(), fn() => $this->call(require $apiFile)),
                ]);
            }

            foreach ($pageFiles as $pageFile) {
                if (!is_file($pageFile)) {
                    continue;
                }

                if (str_starts_with($pageFile, '_')) {
                    continue;
                }

                $pageFilePath = str_replace($path, '', dirname($pageFile));
                $pageFileName = basename($pageFile, '.php');

                if ($pageFileName === 'index') {
                    $pageFileName = '';
                }

                $collector->addRoute('GET', "{$pageFilePath}/{$pageFileName}", [
                    'type' => 'page',
                    'factory' => fn() => $this->applyMiddleware(request(), fn() => $this->call(require $pageFile)),
                ]);
            }
        });

        $httpMethod = $request->getMethod();
        $httpPath = $request->getBaseUrl() . $request->getPathInfoWithoutExtension();

        $routed = $dispatcher->dispatch($httpMethod, $httpPath);

        switch ($routed[0]) {
            case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                throw new \RuntimeException('405');

            case \FastRoute\Dispatcher::FOUND:
                $request->setParams($routed[2]);

                if ($routed[1]['type'] === 'api') {
                    $response = $routed[1]['factory']();
                    $response->send();
                }

                if ($routed[1]['type'] === 'page') {
                    $response = $routed[1]['factory']();

                    if (is_file("{$path}/_document.php")) {
                        $document = require "{$path}/_document.php";
                        $response = $document($request, $response->getContent());

                        if (is_string($response)) {
                            $response = \Next\Http\Response::create($response);
                        }
                    }

                    $response->send();
                }

                break;

            default:
                throw new \RuntimeException('404');
        }
    }

    /**
     * @return \Next\Http\Response|\Next\Http\JsonResponse|\Next\Http\RedirectResponse
     */
    private function applyMiddleware(\Next\Http\Request $request, \Closure $last): mixed
    {
        if (!isset($this['config']['middleware']) || empty($this['config']['middleware'])) {
            return $last($request);
        }

        /**
         * @return \Next\Http\Response|\Next\Http\JsonResponse|\Next\Http\RedirectResponse
         */
        $terminator = function (\Next\Http\Request $request) use ($last): mixed {
            $response = $this->negotiate($last($request));

            if (
                $response instanceof \Next\Http\Response ||
                $response instanceof \Next\Http\JsonResponse ||
                $response instanceof \Next\Http\RedirectResponse
            ) {
                return $response;
            }

            return \Next\Http\Response::create($response);
        };

        $runner = new \Next\Middleware\Runner($this, $this['config']['middleware'], $terminator);

        return $runner($request);
    }

    private function negotiate(mixed $response): mixed
    {
        if ($response instanceof \Next\Http\RequestMethodNegotiator) {
            return $this->negotiate($response->negotiate());
        }
        if ($response instanceof \Next\Http\ResponseTypeNegotiator) {
            return $this->negotiate($response->negotiate());
        }

        return $response;
    }
}
