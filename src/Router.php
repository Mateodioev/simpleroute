<?php

namespace Mateodioev\HttpRouter;

use Mateodioev\HttpRouter\exceptions\{HttpNotFoundException, InvalidReturnException, RequestException};
use Mateodioev\HttpRouter\Server\{NativeServer, Server};
use Mateodioev\StringVars\Config;

class Router
{
    /**
     * Http routes
     * @var array<string, Route[]>
     */
    protected array $routes;

    /**
     * Current base route used for (sub) route mounting
     */
    protected ?string $baseRoute = null;

    protected Server $server;

    public function __construct(protected ?Config $conf = null)
    {
        // Initialize routes array
        foreach (HttpMethods::cases() as $method)
            $this->routes[$method->value] = [];
        $this->server = Container::singleton(NativeServer::class);
    }

    /**
     * @throws HttpNotFoundException
     */
    protected function resolve(Request $request): Route
    {
        foreach ($this->routes[$request->method()->value] as $route) {
            if ($route->match($request->uri())) {
                $route->params($request->uri());
                return $route;
            }
        }

        throw new HttpNotFoundException($request->uri() . ' not found');
    }

    /**
     * @throws RequestException
     * @throws HttpNotFoundException
     */
    public function run(): void
    {
        $request = $this->server->request();
        $route   = $this->resolve($request);
        $action  = $route->action();

        $request->setRoute($route);
        $response = $this->buildCustomResponse($action($request)); // Execute action

        $this->send($response);
    }

    /**
     * Create new response result from custom response
     */
    private function buildCustomResponse(mixed $response): Response
    {
        if ($response instanceof Response) {
            return $response;
        } elseif (\is_string($response)) {
            return Response::text($response);
        } elseif (\is_array($response)) {
            return Response::json($response);
        } elseif (\is_object($response)) {
            return Response::json((array)$response);
        } else {
            throw new InvalidReturnException('Action must return a ' . Response::class . ' instance');
        }
    }

    /**
     * Send response to the server
     */
    public function send(Response $response): void
    {
        $this->server->send($response);
    }

    /**
     * @param callable $action Function to execute when uri match with endpoint called, receives a Request instance as parameter
     */
    protected function addRoute(HttpMethods $method, string $uri, callable $action): static
    {
        $uri = $this->baseRoute . '/' . trim($uri, '/');
        $uri = $this->baseRoute ? rtrim($uri, '/') : $uri;

        $this->routes[$method->value][] = new Route($uri, $action, $this->conf);
        return $this;
    }

    public function setBaseRoute(string $baseRoute): static
    {
        $this->baseRoute = $baseRoute;
        return $this;
    }

    public function mount(string $uri, callable $fn): static
    {
        // Get current base route
        $currentBaseRoute = $this->baseRoute;

        $this->baseRoute .= $uri;
        call_user_func($fn);

        // reset base uri
        $this->baseRoute = $currentBaseRoute;

        return $this;
    }

    /**
     * Handle all methods
     */
    public function all(string $uri, callable $action): static
    {
        foreach (HttpMethods::cases() as $method) {
            $this->addRoute($method, $uri, $action);
        }
        return $this;
    }

    public function get(string $uri, callable $action): static
    {
        return $this->addRoute(HttpMethods::GET, $uri, $action);
    }

    public function post(string $uri, callable $action): static
    {
        return $this->addRoute(HttpMethods::POST, $uri, $action);
    }

    public function put(string $uri, callable $action): static
    {
        return $this->addRoute(HttpMethods::PUT, $uri, $action);
    }

    public function patch(string $uri, callable $action): static
    {
        return $this->addRoute(HttpMethods::PATCH, $uri, $action);
    }

    public function delete(string $uri, callable $action): static
    {
        return $this->addRoute(HttpMethods::DELETE, $uri, $action);
    }

    /**
     * Map static files
     * @var string $baseUri URI to map the files
     * @var string $path Directory
     * @var HttpMethods[] $methods
     */
    public function static(string $baseUri, string $path, array $methods = [HttpMethods::GET]): static
    {
        if (!\is_dir($path))
            throw new RequestException('Invalid static path', 500);

        $path = rtrim($path, '/') . '/'; // Path always end "/"
        $files = $this->getFiles($path);
        $fileParam = 'fileName'; // file param

        $uri = rtrim($baseUri, '/') . '/{' . $fileParam . '}';

        $fn = function (Request $r) use ($fileParam, $files, $uri): Response {
            $fileName = $r->param($fileParam, '""');

            if (in_array($fileName, array_keys($files)) === false) // File not found
                return Response::text('File not found')->setStatus(404);

            // Render the file
            return $this->renderFile($files[$fileName]);
        };

        // Register the routes
        array_map(function (HttpMethods $method) use ($uri, $fn) {
            $this->addRoute($method, $uri, $fn);
        }, $methods);

        return $this;
    }

    /**
     * @return array<string, string> basename => full path
     */
    private function getFiles(string $path): array
    {
        $_files = glob($path . '*');
        $files = [];

        foreach ($_files as $value) {
            $files[\basename($value)] = $value;
        }

        return $files;
    }

    private function renderFile(string $file): Response
    {
        $loader = static function (string $file) {
            return \file_get_contents($file);
        }; // Delete Router context

        return Response::create()
            ->setContentType(\mime_content_type($file))
            ->setContent($loader($file));
    }
}
