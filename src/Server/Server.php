<?php

namespace Mateodioev\HttpRouter\Server;

use Mateodioev\HttpRouter\{Request, Response};

interface Server
{
    /**
     * Get request
     */
    public function request(): Request;

    /**
     * Send response to the user
     */
    public function send(Response $response): void;

    /**
     * Get server name
     */
    public static function name(): string;
}
