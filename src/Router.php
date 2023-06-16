<?php

namespace Mateodioev\HttpRouter;

use Closure;
use Mateodioev\HttpRouter\exceptions\HttpNotFoundException;
use Mateodioev\HttpRouter\exceptions\InvalidReturnException;
use Mateodioev\HttpRouter\exceptions\RequestException;

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
    protected string $baseRoute = '';

    protected NativeServer $server;

    public function __construct()
    {
        // Initialize routes array
        foreach (HttpMethods::cases() as $method) $this->routes[$method->value] = [];
        $this->server = new NativeServer();
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

        throw new HttpNotFoundException();
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
        $response = $action($request); // Execute action
        assert($response instanceof Response, new InvalidReturnException('Action must return a Response instance'));

        $this->send($response);
    }

    public function send(Response $response): void
    {
        $this->server->send($response);
    }

    /**
     * @param Closure $action Function to execute when uri match with endpoint called, receives a Request instance as parameter
     */
    protected function addRoute(HttpMethods $method, string $uri, Closure $action): static
    {
        $this->routes[$method->value][] = new Route($this->baseRoute . $uri, $action);
        return $this;
    }

    public function mount(string $uri, Closure $fn): static
    {
        // Get current base route
        $currentBaseRoute = $this->baseRoute;

        $this->baseRoute .= $uri;
        call_user_func($fn);

        // reset base uri
        $this->baseRoute = $currentBaseRoute;

        return $this;
    }

    public function get(string $uri, Closure $action): static
    {
        return $this->addRoute(HttpMethods::GET, $uri, $action);
    }

    public function post(string $uri, Closure $action): static
    {
        return $this->addRoute(HttpMethods::POST, $uri, $action);
    }

    public function put(string $uri, Closure $action): static
    {
        return $this->addRoute(HttpMethods::PUT, $uri, $action);
    }

    public function patch(string $uri, Closure $action): static
    {
        return $this->addRoute(HttpMethods::PATCH, $uri, $action);
    }

    public function delete(string $uri, Closure $action): static
    {
        return $this->addRoute(HttpMethods::DELETE, $uri, $action);
    }
}