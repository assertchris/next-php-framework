<?php

namespace Next;

use Closure;
use Exception;
use FastRoute;
use Illuminate\Container\Container;
use Next\Database;
use Next\Http\Request;
use Next\Http\Response;
use Whoops;

class App extends Container
{
    private Whoops\Run $whoops;

    public function __construct(array $config = [])
    {
        static::setInstance($this);

        $this->configure($config);
    }

    private function configure(array $config = [])
    {
        if (empty($config['paths']) || empty($config['paths']['pages'])) {
            throw new Exception('You must define paths.pages');
        } else {
            $this->configurePaths($config['paths']);
        }

        if (isset($config['database'])) {
            $this->configureDatabase($config['database']);
        }

        if (isset($config['env']) && $config['env'] === 'dev') {
            $this->configureWhoops();
        }
    }

    private function configurePaths(array $paths = [])
    {
        $this->instance('path.pages', $paths['pages']);

        if (isset($paths['migrations'])) {
            $this->instance('path.migrations', $paths['migrations']);
        }

        if (isset($paths['log'])) {
            $this->instance('path.log', $paths['log']);
        }
    }

    private function configureDatabase(array $database = [])
    {
        $capsule = new Database();
        $capsule->addConnection($database);
        $capsule->bootEloquent();
        $capsule->setAsGlobal();

        $this->instance('db', $capsule);
    }

    public function serve()
    {
        $request = Request::capture();
        $response = Response::create();

        if ($request->expectsJson()) {
            $this->whoops->pushHandler(new Whoops\Handler\JsonResponseHandler());
        } else {
            $this->whoops->pushHandler(new Whoops\Handler\PrettyPageHandler());
        }

        $this->instance('request', $request);
        $this->instance('response', $response);

        $this->route($request, $response);
    }

    private function configureWhoops()
    {
        $this->whoops = new Whoops\Run();
        $this->whoops->register();
    }

    private function route(Request $request, Response $response)
    {
        $path = path('pages');
        $allFiles = files("{$path}/*.php");
        $apiFiles = files("{$path}/api/*.php");
        $pageFiles = array_diff($allFiles, $apiFiles);

        $dispatcher = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $collector) use ($path, $apiFiles, $pageFiles) {
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
            case FastRoute\Dispatcher::NOT_FOUND:
                dd('not found');
                break;

            case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                dd('method not allowed');
                break;

            case FastRoute\Dispatcher::FOUND:
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
        }
    }
}
