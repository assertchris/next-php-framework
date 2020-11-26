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

        $this->route($request);
    }

    /**
     * @return \Next\Http\Response|\Next\Http\JsonResponse|\Next\Http\RedirectResponse
     */
    private function applyMiddleware(\Next\Http\Request $request, callable $next): mixed
    {
        if (!isset($this['config']['middleware']) || empty($this['config']['middleware'])) {
            return $next($request);
        }

        /**
         * @return \Next\Http\Response|\Next\Http\JsonResponse|\Next\Http\RedirectResponse
         */
        $final = function (\Next\Http\Request $request) use ($next): mixed {
            $response = $this->negotiateResponse($next($request));

            if (
                $response instanceof \Next\Http\Response ||
                $response instanceof \Next\Http\JsonResponse ||
                $response instanceof \Next\Http\RedirectResponse
            ) {
                return $response;
            }

            return response($response);
        };

        $runner = new class ($this, $this['config']['middleware'], $final) {
            private \Next\App $app;
            private array $middleware;
            private \Closure $final;

            public function __construct(\Next\App $app, array $middleware, \Closure $final)
            {
                $this->app = $app;
                $this->middleware = $middleware;
                $this->final = $final;
            }

            /**
             * @return \Next\Http\Response|\Next\Http\JsonResponse|\Next\Http\RedirectResponse
             */
            public function __invoke(\Next\Http\Request $request): mixed
            {
                if (empty($this->middleware)) {
                    return ($this->final)($request);
                }

                $current = array_shift($this->middleware);
                $next = clone $this;

                return $this->app->make($current)->handle($request, $next);
            }
        };

        return $runner($request);
    }

    private function route(\Next\Http\Request $request)
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
                break;

            case \FastRoute\Dispatcher::FOUND:
                $request->setParams($routed[2]);

                if ($routed[1]['type'] === 'api') {
                    $response = $routed[1]['factory']($request);
                    $response->send();
                }

                if ($routed[1]['type'] === 'page') {
                    $response = $routed[1]['factory']($request);

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
                break;
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
}
