<?php
declare(strict_types=1);

namespace App\Admin\Core;

use Core\Request;

/**
 * Simple Router for admin API (Laravel-style)
 *
 * - Supports HTTP method + path matching
 * - Supports route groups with common prefixes
 * - Handlers are callables: function(Request $request, array $params): mixed
 */
class Router
{
    /** @var array<int, array{method:string,pattern:string,regex:string,handler:callable}> */
    private array $routes = [];

    /** @var string|null */
    private ?string $groupPrefix = null;

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

    public function patch(string $path, callable $handler): void
    {
        $this->addRoute('PATCH', $path, $handler);
    }

    public function group(string $prefix, callable $callback): void
    {
        $previousPrefix = $this->groupPrefix;
        $this->groupPrefix = rtrim(($previousPrefix ?? '') . '/' . ltrim($prefix, '/'), '/');

        $callback($this);

        $this->groupPrefix = $previousPrefix;
    }

    private function addRoute(string $method, string $path, callable $handler): void
    {
        $fullPath = $this->normalizePath(
            ($this->groupPrefix ? '/' . trim($this->groupPrefix, '/') : '') . '/' . ltrim($path, '/')
        );

        $this->routes[] = [
            'method'  => strtoupper($method),
            'pattern' => $fullPath,
            'regex'   => $this->patternToRegex($fullPath),
            'handler' => $handler,
        ];
    }

    private function normalizePath(string $path): string
    {
        $path = '/' . trim($path, '/');
        return $path === '/' ? '/' : rtrim($path, '/');
    }

    private function patternToRegex(string $pattern): string
    {
        // Replace ":param" with named capture groups
        $regex = preg_replace('#:([a-zA-Z_][a-zA-Z0-9_]*)#', '(?P<$1>[^/]+)', $pattern);
        return '#^' . $regex . '$#';
    }

    /**
     * Dispatch the request to the first matching route.
     * Returns whatever the handler returns.
     */
    public function dispatch(Request $request): mixed
    {
        $method = $request->getMethod();
        $uri    = $request->getUri();

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['regex'], $uri, $matches)) {
                $params = [];
                foreach ($matches as $key => $value) {
                    if (!is_int($key)) {
                        $params[$key] = $value;
                    }
                }

                // Expose params on the Request object as well
                $request->setRouteParams($params);

                // Let handler work with Request and route params
                return ($route['handler'])($request, $params);
            }
        }

        // No route matched
        return null;
    }
}
