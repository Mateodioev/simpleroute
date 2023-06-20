<?php

namespace Mateodioev\HttpRouter\Server;

use Mateodioev\HttpRouter\exceptions\RequestException;
use Mateodioev\HttpRouter\{Request, HttpMethods, Response};

use function parse_url, file_get_contents;
use function header, header_remove, http_response_code;

class NativeServer implements Server
{
    const POWERED_BY = 'NativeServer';

    /**
     * @throws RequestException
     */
    public function request(): Request
    {
        return (new Request())
            ->setUrl($_SERVER['REQUEST_URI'])
            ->setUri($this->requestUri())
            ->setMethod($this->requestMethod())
            ->setHeaders($this->requestHeaders())
            ->setData($this->postData())
            ->setBody($this->postBody())
            ->setFiles($this->postFiles())
            ->setQuery($this->queryParams());
    }

    protected function requestUri(): string
    {
        // URI never ends with a slash
        // / = /
        // /foo = /foo
        // /foo/ = /foo
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        if ($uri !== '/') $uri = rtrim($uri, '/');

        return \rawurldecode($uri);
    }

    /**
     * @throws RequestException
     */
    protected function requestMethod(): HttpMethods
    {
        try {
            return HttpMethods::from($_SERVER['REQUEST_METHOD']);
        } catch (\Exception) {
            throw new RequestException('Invalid request method');
        }
    }

    protected function requestHeaders(): array
    {
        return getallheaders();
    }

    /**
     * @return array Form data
     */
    protected function postData(): array
    {
        return $_POST;
    }

    /**
     * @return string Raw body
     */
    protected function postBody(): string
    {
        return file_get_contents('php://input');
    }

    protected function postFiles(): array
    {
        return $_FILES ?? [];
    }

    protected function queryParams(): array
    {
        return $_GET;
    }

    public function send(Response $response): void
    {
        // PHP sends Content-Type header automatically, but it has to removed if
        // the response has not content. Content-type header can't be removed
        // unless it is set to some value before.
        header('Content-Type: None');
        header_remove('Content-Type');
        header('X-Powered-By: ' . self::POWERED_BY);

        $response->prepare();
        http_response_code($response->status());

        foreach ($response->headers() as $header => $value) {
            $value = join(', ', $value);
            header("$header: $value");
        }

        print($response->content());
    }

    public static function name(): string
    {
        return self::POWERED_BY;
    }
}
