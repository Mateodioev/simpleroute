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

// Conditional params
$router->get('/page/{all:id}?', function (Request $r) {
    return Response::text($r->param('id') ?? 'default page');
});

// Mandatory params
$router->get('/usr/{name}', function (Request $r) {
    return Response::text('Hello ' . $r->param('name'));
});

try {
    $router->run();
} catch (HttpNotFoundException $e) {
    $router->send(Response::text($e->getMessage() ?? 'Not found')->setStatus(404));
} catch (RequestException $e) {
    $router->send(Response::text($e->getMessage() ?? 'Server error')->setStatus(500));
}
