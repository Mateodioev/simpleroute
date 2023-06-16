<?php

namespace Mateodioev\HttpRouter\Server;

use Mateodioev\HttpRouter\{HttpMethods, Request, Response};

class FakeServer implements Server
{
    /**
     * Get request
     */
    public function request(): Request
    {
        return (new Request())
            ->setUrl('http://0.0.0.0:8080/usr/mateo')
            ->setUri('/usr/mateo')
            ->setMethod(HttpMethods::GET)
            ->setHeaders([])
            ->setData([])
            ->setBody('')
            ->setFiles([])
            ->setQuery([]);
    }

    /**
     * Send response to the user
     */
    public function send(Response $response): void
    {
        header('Content-Type: None');
        header_remove('Content-Type');
        header('X-Powered-By: fake server');

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
        return 'fake server';
    }
}
