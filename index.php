<?php

use Mateodioev\HttpRouter\exceptions\{HttpNotFoundException, RequestException};
use Mateodioev\HttpRouter\{Response, Router};

require __DIR__ . '/vendor/autoload.php';

$router = new Router();

$router->get('/', function () {
    return Response::json(['message' => 'Hello World!']);
});

try {
    $router->run();
} catch (HttpNotFoundException|RequestException $e) {
    $router->send(Response::text($e->getMessage())->setStatus(500));
}
