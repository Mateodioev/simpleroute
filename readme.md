# Simple HTTP router

> :warning: **This project was made for educational purposes only.** It is not intended to be used in production environments.

## Usage

```php
use Mateodioev\HttpRouter\exceptions\{HttpNotFoundException, RequestException};
use Mateodioev\HttpRouter\{Request, Response, Router};

$router = new Router();

// Register your endpoints here
$router->get('/', function (Request $r) {
    return Response::text('Hello world!');
});

$router->run();
```

### Methods
You can use the `Router::get`, `Router::post`, `Router::put`, `Router::patch` and `Router::delete` methods to register your endpoints.

```php
$router->myHttpMethod($uri, $callback);
```
- `$uri` is the path of the endpoint.
- `$callback` is the function that will be executed when the endpoint is requested. Each callback (action) must return an instance of the Mateodioev\HttpRouter\Response class or an InvalidReturnException will be thrown.

#### Static files
You can map all static files in a directory with the `static` method.

```php
$router->static($baseUri, $path, $methods);
// Default methods are GET
```

Example:

```bash
tree
```
```text
.
├── index.php
└── styles.css

1 directory, 2 files
```

```php
$router->static('/docs', 'public/');
```
Now you can reach this uris

- /docs/index.php
- /docs/styles.css


#### Handling all HTTP methods
You can use the `Router::all` method to handle all HTTP methods with one callback.

```php
$router->all($uri, $callback);
```

### Path parameters

You can use path parameters in your endpoints. Path parameters are defined by a bracket followed by the name of the parameter.
> `/users/{id}`

```php
$router->get('/page/{name}', function (Request $r) {
    return Response::text('Welcome to ' . $r->param('name'));
});
```

**Note:** You can make a parameter optional by adding a question mark after the name of the parameter.
> `/users/{id}?`

```php
$router->get('/page/{name}?', function (Request $r) {
    $pageName = $r->param('name') ?? 'home'; // If the parameter is not present, the method return null
    return Response::text('Welcome to ' . $pageName);
});
```

### Request data

You can get all data from the request with the following methods:

- `Request::method()` returns the HTTP method of the request.
- `Request::uri()` returns the URI of the request.
- `Request::uri()` returns the URL of the request.
- `Request::param($name, $default = null)` returns the value of the parameter with the name `$name` or null if the parameter is not present.
- `Request::params()` return al the request URI parameters.
- `Request::headers()` returns an array with all the headers of the request.
- `Request::body()` returns the body of the request (from [php://input](https://www.php.net/manual/en/wrappers.php.php)).
- `Request::data()` returns an array with all the data of the request. Use this when Content-Type is _application/x-www-form-urlencoded_ or _multipart/form-data_.
- `Request::files()` returns an array with all the files of the request.
- `Request::query()` returns an array with all the query parameters from the uri.


*TODO list:*
- [ ] Add support for middlewares.
- [ ] Add support for custom error handlers.
