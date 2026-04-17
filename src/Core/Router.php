<?php
declare(strict_types=1);

namespace App\Core;

class Router
{
    /** @var array<string, array<int, array{pattern:string, paramNames:array, handler:callable}>> */
    private array $routes = [];

    /** @var string */
    private string $currentGroupPrefix = '';

    public function get(string $path, callable $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, callable $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    public function put(string $path, callable $handler): void
    {
        $this->addRoute('PUT', $path, $handler);
    }

    public function delete(string $path, callable $handler): void
    {
        $this->addRoute('DELETE', $path, $handler);
    }

    public function group(string $prefix, callable $callback): void
    {
        $previous = $this->currentGroupPrefix;
        $this->currentGroupPrefix = rtrim($previous . $prefix, '/');
        if ($this->currentGroupPrefix === '') {
            $this->currentGroupPrefix = '/';
        }

        $callback($this);

        $this->currentGroupPrefix = $previous;
    }

    public function dispatch(Request $request): mixed
    {
        $method = strtoupper($request->getMethod());
        $uri    = $request->getUri();

        $routesForMethod = $this->routes[$method] ?? [];

        foreach ($routesForMethod as $route) {
            if (preg_match($route['pattern'], $uri, $matches)) {
                $params = [];
                foreach ($route['paramNames'] as $name) {
                    if (isset($matches[$name])) {
                        $params[$name] = $matches[$name];
                    }
                }

                $handler = $route['handler'];

                // Support closures and [Controller, 'method'] callables with 1 or 2 params
                if ($handler instanceof Closure) {
                    $ref = new ReflectionFunction($handler);
                } elseif (is_array($handler) && count($handler) === 2) {
                    $ref = new ReflectionMethod($handler[0], $handler[1]);
                } else {
                    // Fallback: call with (Request, params)
                    return $handler($request, $params);
                }

                $paramCount = $ref->getNumberOfParameters();

                if ($paramCount >= 2) {
                    return $handler($request, $params);
                }

                return $handler($request);
            }
        }

        return null;
    }

    private function addRoute(string $method, string $path, callable $handler): void
    {
        $method = strtoupper($method);

        $fullPath = $this->normalizePath($this->currentGroupPrefix, $path);

        // Convert path like /api/v1/products/:id to regex with named group
        $paramNames = [];
        $pattern = preg_replace_callback('#:([a-zA-Z_][a-zA-Z0-9_]*)#', function ($m) use (&$paramNames) {
            $paramNames[] = $m[1];
            return '(?P<' . $m[1] . '>[^/]+)';
        }, $fullPath);

        $regex = '#^' . $pattern . '$#';

        $this->routes[$method][] = [
            'pattern'    => $regex,
            'paramNames' => $paramNames,
            'handler'    => $handler,
        ];
    }

    private function normalizePath(string $prefix, string $path): string
    {
        $prefix = rtrim($prefix, '/');
        $path   = '/' . ltrim($path, '/');

        if ($prefix === '' || $prefix === '/') {
            return $path;
        }

        return $prefix . $path;
    }
}
