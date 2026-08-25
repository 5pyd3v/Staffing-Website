<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    /** @var array<int, array{method:string, pattern:string, regex:string, handler:mixed, middleware:array}> */
    private array $routes = [];

    public function get(string $path, mixed $handler, array $middleware = []): void
    {
        $this->add('GET', $path, $handler, $middleware);
    }

    public function post(string $path, mixed $handler, array $middleware = []): void
    {
        $this->add('POST', $path, $handler, $middleware);
    }

    public function put(string $path, mixed $handler, array $middleware = []): void
    {
        $this->add('PUT', $path, $handler, $middleware);
    }

    public function patch(string $path, mixed $handler, array $middleware = []): void
    {
        $this->add('PATCH', $path, $handler, $middleware);
    }

    public function delete(string $path, mixed $handler, array $middleware = []): void
    {
        $this->add('DELETE', $path, $handler, $middleware);
    }

    private function add(string $method, string $path, mixed $handler, array $middleware): void
    {
        $path = '/' . trim($path, '/');
        $regex = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $path);
        $regex = $path === '/' ? '#^/$#' : '#^' . $regex . '$#';

        $this->routes[] = [
            'method' => $method,
            'pattern' => $path,
            'regex' => $regex,
            'handler' => $handler,
            'middleware' => $middleware,
        ];
    }

    public function dispatch(Request $request): void
    {
        $method = $request->method();
        $path = $request->path();

        $allowedMethodsForPath = [];

        foreach ($this->routes as $route) {
            if (!preg_match($route['regex'], $path, $matches)) {
                continue;
            }

            $allowedMethodsForPath[] = $route['method'];

            if ($route['method'] !== $method) {
                continue;
            }

            $params = array_filter(
                $matches,
                static fn ($key) => is_string($key),
                ARRAY_FILTER_USE_KEY
            );
            $request->setRouteParams($params);

            $this->runMiddleware($route['middleware'], $request);
            $this->invoke($route['handler'], $request);
            return;
        }

        if ($allowedMethodsForPath !== []) {
            Response::abort(405, 'Method not allowed');
        }

        Response::abort(404, 'Page not found');
    }

    private function runMiddleware(array $middleware, Request $request): void
    {
        foreach ($middleware as $entry) {
            [$name, $arg] = array_pad(explode(':', $entry, 2), 2, null);
            $class = 'App\\Middleware\\' . ucfirst($name) . 'Middleware';

            if (!class_exists($class)) {
                continue;
            }

            /** @var object $instance */
            $instance = new $class();
            $instance->handle($request, $arg);
        }
    }

    private function invoke(mixed $handler, Request $request): void
    {
        if (is_array($handler)) {
            [$class, $method] = $handler;
            $controller = new $class();
            $controller->$method($request);
            return;
        }

        if (is_callable($handler)) {
            $handler($request);
            return;
        }

        Response::abort(500, 'Invalid route handler');
    }
}
