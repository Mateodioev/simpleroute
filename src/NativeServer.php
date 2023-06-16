<?php

namespace Mateodioev\HttpRouter;

use Mateodioev\HttpRouter\exceptions\RequestException;

use function parse_url, file_get_contents;
use function header, header_remove, http_response_code;

class NativeServer
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
        return parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
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
}