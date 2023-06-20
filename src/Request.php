<?php

namespace Mateodioev\HttpRouter;

use Mateodioev\HttpRouter\exceptions\RequestException;

/**
 * Incoming Http request
 */
final class Request
{
    /**
     * @var string $url Full url
     */
    protected string $url;

    /**
     * @var string $uri Uri without query string (path)
     */
    protected string $uri;

    /**
     * @var HttpMethods $method Request method
     */
    protected HttpMethods $method;

    /**
     * @var array $headers Request headers
     */
    protected array $headers;

    /**
     * @var string $body Request body from php://input (not available for GET requests)
     */
    protected string $body;

    /**
     * @var array $data Form data (not available for GET requests)
     */
    protected array $data;

    /**
     * @var array $files Files data (not available for GET requests)
     */
    protected array $files;

    /**
     * @var array $query Query string data (from url)
     */
    protected array $query;


    private Route $route;
    private array $params = [];

    public function setRoute(Route $route): static
    {
        $this->route = $route;
        $this->params = $route->params();
        return $this;
    }

    public function route(): Route
    {
        return $this->route;
    }

    /**
     * Get param from uri
     * @param string $name Param to get
     * @param mixed $default If param not found, return this
     */
    public function param(string $name, mixed $default = null): mixed
    {
        return $this->params[$name] ?? $default;
    }

    /**
     * Get all uri params
     */
    public function params(): array
    {
        return $this->params;
    }

    /**
     * Full url
     */
    public function url(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): Request
    {
        $this->url = $url;
        return $this;
    }

    /**
     * Query string data (from url)
     */
    public function query(): array
    {
        return $this->query;
    }

    public function setQuery(array $query): Request
    {
        $this->query = $query;
        return $this;
    }

    /**
     * Files data (not available for GET requests)
     * @throws RequestException
     */
    public function files(): array
    {
        if ($this->method() === HttpMethods::GET)
            throw new RequestException('Body is not available for GET requests');
        return $this->files;
    }

    public function setFiles(array $files): Request
    {
        $this->files = $files;
        return $this;
    }

    /**
     * Form data (not available for GET requests)
     * @throws RequestException
     */
    public function data(): array
    {
        if ($this->method() === HttpMethods::GET)
            throw new RequestException('Body is not available for GET requests');
        return $this->data;
    }

    public function setData(array $data): Request
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Request body from php://input (not available for GET requests)
     * @throws RequestException
     */
    public function body(): string
    {
        if ($this->method() === HttpMethods::GET)
            throw new RequestException('Body is not available for GET requests');

        return $this->body;
    }

    public function setBody(string $body): Request
    {
        $this->body = $body;
        return $this;
    }

    /**
     * Request headers
     */
    public function headers(): array
    {
        return $this->headers;
    }

    public function setHeaders(array $headers): Request
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * Uri without query string (path)
     */
    public function uri(): string
    {
        return $this->uri;
    }

    public function setUri(string $uri): Request
    {
        $this->uri = $uri;
        return $this;
    }

    /**
     * Request method
     */
    public function method(): HttpMethods
    {
        return $this->method;
    }

    public function setMethod(HttpMethods $method): Request
    {
        $this->method = $method;
        return $this;
    }
}
