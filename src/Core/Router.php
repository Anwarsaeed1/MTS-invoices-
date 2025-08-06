<?php
namespace AnwarSaeed\InvoiceProcessor\Core;

class Router
{
    private array $routes = [];
    private array $errorHandlers = [];

    public function add(string $method, string $path, callable $handler): void
    {
        $this->routes[$method][$path] = $handler;
    }

    public function addErrorHandler(int $code, callable $handler): void
    {
        $this->errorHandlers[$code] = $handler;
    }

 // src/Core/Router.php
public function dispatch(): void
{
    $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $requestMethod = $_SERVER['REQUEST_METHOD'];

    foreach ($this->routes[$requestMethod] ?? [] as $route => $handler) {
        // Convert route pattern to regex
        $pattern = preg_replace('/\{([a-z]+)\}/', '(?P<$1>[^/]+)', $route);
        $pattern = "@^" . $pattern . "$@D";

        if (preg_match($pattern, $requestUri, $matches)) {
            // Filter named parameters only
            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
            $handler($params);
            return;
        }
    }

    $this->handleError(404);
}

    private function convertToPattern(string $route): string
    {
        $pattern = preg_replace('/\{([a-z]+)\}/', '(?P<$1>[^/]+)', $route);
        return "@^" . $pattern . "$@D";
    }

    private function handleError(int $code): void
    {
        if (isset($this->errorHandlers[$code])) {
            $this->errorHandlers[$code]();
            return;
        }
        
        http_response_code($code);
        echo "Error $code";
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }
}