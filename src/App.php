<?php

namespace Next;

use Closure;
use Exception;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Illuminate\Container\Container;
use Next\Database\Manager;
use Next\Http\Request;
use Next\Http\Response;
use function FastRoute\simpleDispatcher;

class App extends Container
{
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
    }

    private function configurePaths(array $paths = [])
    {
        $this->instance('path.pages', $paths['pages']);

        if (isset($paths['migrations'])) {
            $this->instance('path.migrations', $paths['migrations']);
        }
    }

    private function configureDatabase(array $database = [])
    {
        $capsule = new Manager();
        $capsule->addConnection($database);
        $capsule->bootEloquent();
        $capsule->setAsGlobal();

        $this->instance('db', $capsule);
    }

    public function serve()
    {
        $request = Request::capture();
        $response = Response::create();

        $this->instance('request', $request);
        $this->instance('response', $response);

        $this->route($request, $response);
    }

    private function route(Request $request, Response $response)
    {
        $path = path('pages');
        $allFiles = files("{$path}/*.php");
        $apiFiles = files("{$path}/api/*.php");
        $pageFiles = array_diff($allFiles, $apiFiles);

        $dispatcher = simpleDispatcher(function (RouteCollector $collector) use ($path, $apiFiles, $pageFiles) {
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
            case Dispatcher::NOT_FOUND:
                dd('not found');
                break;

            case Dispatcher::METHOD_NOT_ALLOWED:
                dd('method not allowed');
                break;

            case Dispatcher::FOUND:
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
