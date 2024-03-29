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

    /**
     * @return \Next\Http\Response|\Next\Http\JsonResponse|\Next\Http\RedirectResponse
     */
    public function serve(\Next\Http\Request $request = null): mixed
    {
        $request = $request ?? \Next\Http\Request::capture();
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

        return $this->route($request);
    }

    /**
     * @return \Next\Http\Response|\Next\Http\JsonResponse|\Next\Http\RedirectResponse
     */
    private function route(\Next\Http\Request $request): mixed
    {
        $path = path('pages');
        $allFiles = files($path);
        $apiFiles = files("{$path}/api");
        $pageFiles = array_diff($allFiles, $apiFiles);

        $errors = [
            '_404' => fn() => \Next\Http\Response::create('Not found.', 404),
            '_405' => fn() => \Next\Http\Response::create('Method not allowed.', 405),
            '_415' => fn() => \Next\Http\Response::create('Content type not supported.', 415),
            '_500' => fn() => \Next\Http\Response::create('Something went wrong.', 500),
        ];

        $dispatcher = \FastRoute\simpleDispatcher(function (\FastRoute\RouteCollector $collector) use (
            $request,
            $path,
            $apiFiles,
            $pageFiles,
            &$errors,
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
                    'factory' => fn() => $this->applyMiddleware($request, fn() => $this->call(require $apiFile)),
                ]);
            }

            foreach ($pageFiles as $pageFile) {
                if (!is_file($pageFile)) {
                    continue;
                }

                $pageFilePath = str_replace($path, '', dirname($pageFile));
                $pageFileName = basename($pageFile, '.php');

                if (str_starts_with($pageFileName, '_')) {
                    if (array_key_exists($pageFileName, $errors)) {
                        $errors[$pageFileName] = require $pageFile;
                    }

                    continue;
                }

                if ($pageFileName === 'index') {
                    $pageFileName = '';
                }

                $collector->addRoute('GET', "{$pageFilePath}/{$pageFileName}", [
                    'type' => 'page',
                    'factory' => fn() => $this->applyMiddleware($request, fn() => $this->call(require $pageFile)),
                ]);
            }
        });

        $httpMethod = $request->getMethod();
        $httpPath = $request->getBaseUrl() . $request->getPathInfoWithoutExtension();

        $routed = $dispatcher->dispatch($httpMethod, $httpPath);

        try {
            return match ($routed[0]) {
                \FastRoute\Dispatcher::METHOD_NOT_ALLOWED => $this->call($errors['_405']),
                \FastRoute\Dispatcher::FOUND => $this->dispatch($request, $routed, $path),
                default => $this->call($errors['_404']),
            };
        } catch (\Next\Http\MissingContentNegotiator) {
            return $this->call($errors['_405']);
        } catch (\Next\Http\UnsupportedContentType) {
            return $this->call($errors['_415']);
        } catch (\Throwable) {
            return $this->call($errors['_500']);
        }
    }

    /**
     * @return \Next\Http\Response|\Next\Http\JsonResponse|\Next\Http\RedirectResponse
     */
    private function dispatch(\Next\Http\Request $request, array $routed, string $path): mixed
    {
        $request->setParams($routed[2]);

        $response = $routed[1]['factory']();

        if (is_string($response)) {
            $response = \Next\Http\Response::create($response);
        }

        if ($routed[1]['type'] === 'page' && is_file("{$path}/_document.php")) {
            $document = require "{$path}/_document.php";

            $originalResponse = $response;
            $response = $this->call($document, [
                $response,
                'content' => $response->getContent(),
            ]);

            if (is_string($response)) {
                $response = new \Next\Http\Response($response);
                $response->headers->add($originalResponse->headers->all());
            }
        }

        return $response;
    }

    /**
     * @return \Next\Http\Response|\Next\Http\JsonResponse|\Next\Http\RedirectResponse
     */
    private function applyMiddleware(\Next\Http\Request $request, \Closure $last): mixed
    {
        if (!isset($this['config']['middleware']) || empty($this['config']['middleware'])) {
            return $this->negotiate($request, $last($request));
        }

        /**
         * @return \Next\Http\Response|\Next\Http\JsonResponse|\Next\Http\RedirectResponse
         */
        $terminator = function (\Next\Http\Request $request) use ($last): mixed {
            $response = $this->negotiate($request, $last($request));

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

    /**
     * @param \Next\Http\Response|\Next\Http\JsonResponse|\Next\Http\RedirectResponse|\Next\Http\RequestMethodNegotiator|\Next\Http\ResponseTypeNegotiator $response
     *
     * @return \Next\Http\Response|\Next\Http\JsonResponse|\Next\Http\RedirectResponse|string
     */
    private function negotiate(\Next\Http\Request $request, mixed $response): mixed
    {
        if ($response instanceof \Next\Http\RequestMethodNegotiator) {
            return $this->negotiate($request, $response->negotiate($request->getMethod()));
        }

        if ($response instanceof \Next\Http\ResponseTypeNegotiator) {
            return $this->negotiate($request, $response->negotiate($request->getPathInfoExtension()));
        }

        return $response;
    }
}
