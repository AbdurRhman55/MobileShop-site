<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Regex-based router.
 *
 * Patterns use {param} placeholders. Supported HTTP verbs: GET, POST.
 */
final class Router
{
    /** @var array<int, array{method:string, pattern:string, regex:string, handler:mixed, name:?string}> */
    private array $routes = [];

    public function __construct(private readonly Request $request)
    {
    }

    public function get(string $pattern, callable|array $handler, ?string $name = null): void
    {
        $this->add('GET', $pattern, $handler, $name);
    }

    public function post(string $pattern, callable|array $handler, ?string $name = null): void
    {
        $this->add('POST', $pattern, $handler, $name);
    }

    private function add(string $method, string $pattern, callable|array $handler, ?string $name): void
    {
        $regex = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $pattern);
        $this->routes[] = [
            'method'  => $method,
            'pattern' => $pattern,
            'regex'   => '#^' . $regex . '$#u',
            'handler' => $handler,
            'name'    => $name,
        ];
    }

    public function dispatch(): void
    {
        $path   = $this->request->path;
        $method = $this->request->method;
        $trailingSlashUri = $this->request->server['REQUEST_URI'] ?? '/';

        // Trailing-slash consistency: redirect /foo/ -> /foo with 301.
        if ($path !== '/' && str_ends_with($trailingSlashUri, '/')) {
            $target = $path . '?' . ($this->request->server['QUERY_STRING'] ?? '');
            HttpRedirect::to(rtrim($target, '?'), 301);
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            if (preg_match($route['regex'], $path, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                $params = array_map('rawurldecode', $params);

                if (is_array($route['handler'])) {
                    [$class, $action] = $route['handler'];
                    $controller = new $class($this->request, $this);
                    $controller->$action(...array_values($params));
                } else {
                    ($route['handler'])($params, $this->request, $this);
                }
                return;
            }
        }

        // No route matched: consult the redirect map, else genuine 404.
        $this->handleUnmatched($path);
    }

    private function handleUnmatched(string $path): void
    {
        $redirect = \App\Models\Redirect::resolve($path);
        if ($redirect !== null) {
            HttpRedirect::to($redirect['to_path'], (int) $redirect['status_code']);
        }
        throw new Http(404, 'The page you are looking for does not exist or has been moved.');
    }
}
