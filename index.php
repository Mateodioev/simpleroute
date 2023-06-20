<?php

use Mateodioev\HttpRouter\exceptions\{HttpNotFoundException, RequestException};
use Mateodioev\HttpRouter\{Request, Response, Router};

require __DIR__ . '/vendor/autoload.php';

$conf = new \Mateodioev\StringVars\Config;
$conf->addFormat('uuid', '([0-9a-fA-F]{8}\b-[0-9a-fA-F]{4}\b-[0-9a-fA-F]{4}\b-[0-9a-fA-F]{4}\b-[0-9a-fA-F]{12})');

$router = new Router($conf);

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

// Using custom format
$router->get('/user/{uuid:id}', function (Request $r) {
    return Response::text('Hello user id ' . $r->param('id'));
});
// try: /usr/123e4567-e89b-12d3-a456-426655440000

try {
    $router->run();
} catch (HttpNotFoundException $e) {
    $router->send(Response::text($e->getMessage() ?? 'Not found')->setStatus(404));
} catch (RequestException $e) {
    $router->send(Response::text($e->getMessage() ?? 'Server error')->setStatus(500));
}
