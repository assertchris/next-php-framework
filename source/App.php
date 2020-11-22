<?php

namespace Next;

class App extends \Illuminate\Container\Container
{
    private $proxies = [
        'Next\\Cache' => \Next\Cache\Proxy::class,
        'Next\\Database' => \Next\Database\Proxy::class,
        'Next\\Errors' => \Next\Errors\Proxy::class,
        'Next\\Logging' => \Next\Logging\Proxy::class,
        'Next\\Session' => \Next\Session\Proxy::class,
        'Next\\Validation' => \Next\Validation\Proxy::class,
    ];

    public function __construct(array $config = [])
    {
        static::setInstance($this);
        $this->configure($config);
    }

    private function configure(array $config = [])
    {
        $this['config'] = $config;

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
        foreach ($this->proxies as $alias => $class) {
            $class::connect($this);
            class_alias($class, $alias);
        }
    }

    public function serve()
    {
        $request = \Next\Http\Request::capture();
        $response = \Next\Http\Response::create();

        if ($request->expectsJson()) {
            $this['errors']->enableJsonHandler($this);
        } else {
            $this['errors']->enableHtmlHandler($this);
        }

        $this->instance('request', $request);
        $this->instance('response', $response);

        $this->route($request, $response);
    }

    private function route(\Next\Http\Request $request, \Next\Http\Response $response)
    {
        $path = path('pages');
        $allFiles = files("{$path}/*.php");
        $apiFiles = files("{$path}/api/*.php");
        $pageFiles = array_diff($allFiles, $apiFiles);

        $dispatcher = \FastRoute\simpleDispatcher(function (\FastRoute\RouteCollector $collector) use ($path, $apiFiles, $pageFiles) {
            foreach ($apiFiles as $apiFile) {
                $apiFilePath = str_replace($path, '', dirname($apiFile));
                $apiFileName = basename($apiFile, '.php');

                if ($apiFileName === 'index') {
                    $apiFileName = '';
                }

                $collector->addRoute('*', "{$apiFilePath}/{$apiFileName}", ['type' => 'api', 'factory' => require $apiFile]);
            }

            foreach ($pageFiles as $pageFile) {
                $pageFilePath = str_replace($path, '', dirname($pageFile));
                $pageFileName = basename($pageFile, '.php');

                if ($pageFileName === 'index') {
                    $pageFileName = '';
                }

                $collector->addRoute('GET', "{$pageFilePath}/{$pageFileName}", ['type' => 'page', 'factory' => require $pageFile]);
            }
        });

        $httpMethod = $request->getMethod();
        $httpPath = $request->getBaseUrl() . $request->getPathInfo();

        $result = $dispatcher->dispatch($httpMethod, $httpPath);

        switch ($result[0]) {
            case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                throw new \RuntimeException('405');
                break;

            case \FastRoute\Dispatcher::FOUND:
                if ($result[1]['type'] === 'api') {
                    $result[1]['factory']($request, $response);
                }

                if ($result[1]['type'] === 'page') {
                    $content = $result[1]['factory']($request, $response);

                    if (is_file("{$path}/_document.php")) {
                        $document = require "{$path}/_document.php";
                        $content = $document($request, $response, $content);
                    }

                    $response->setContent($content);
                    $response->send();
                }

                break;
            
            default:
                throw new \RuntimeException('404');
                break;
        }
    }
}
