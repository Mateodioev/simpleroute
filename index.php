<?php

use Mateodioev\HttpRouter\exceptions\{HttpNotFoundException, RequestException};
use Mateodioev\HttpRouter\{Request, Response, Router};

require __DIR__ . '/vendor/autoload.php';

$router = new Router();

$router->get('/', function () {
    return Response::text('Hello world!')
        ->setHeader('X-message', 'Method GET');
});

$router->post('/', function () {
    return Response::json(['message' => 'Hello World!'])
        ->setHeader('X-message', 'Method POST');
});

$router->get('/usr/{name}?', function (Request $r) {
    return Response::json($r->param('name', true));
});

try {
    $router->run();
} catch (HttpNotFoundException | RequestException $e) {
    $router->send(Response::text($e->getMessage() ?? '')->setStatus(500));
}
