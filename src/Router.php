<?php
namespace Df3g\Router;

class Router {
    private $routes = [];
    private $notFoundHandler;

    /**
     * Add a new route
     * 
     * @param string $method HTTP method (GET, POST, etc.)
     * @param string $path Route path
     * @param callable $handler Route handler
     * @return self
     */
    public function addRoute($method, $path, $handler) {
        // Normalize the path and method
        $method = strtoupper($method);
        $path = trim($path, '/');
        
        // Store the route with a pattern for parameter matching
        $this->routes[] = [
            'method' => $method,
            'path' => $this->convertPathToRegex($path),
            'handler' => $handler,
            'params' => $this->extractParams($path),
            'optionalParams' => $this->extractOptionalParams($path)
        ];
        
        return $this;
    }

    /**
     * Set a Not Found handler
     * 
     * @param callable $handler Not found handler function
     * @return self
     */
    public function setNotFoundHandler($handler) {
        $this->notFoundHandler = $handler;
        return $this;
    }

    /**
     * Dispatch the current request
     * 
     * @param string $requestMethod
     * @param string $requestUri
     * @return mixed
     */
    public function dispatch($requestMethod = null, $requestUri = null) {
        // Use current request if not provided
        $requestMethod = $requestMethod ?? $_SERVER['REQUEST_METHOD'];
        $requestUri = $requestUri ?? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Normalize the request URI
        $requestUri = trim($requestUri, '/');

        // Try to match a route
        foreach ($this->routes as $route) {
            if ($route['method'] === $requestMethod && 
                preg_match($route['path'], $requestUri, $matches)) {
                
                // Prepare parameters
                $params = [];
                $paramIndex = 1; // Start from 1 as 0 is full match

                // Handle required parameters
                foreach ($route['params'] as $name) {
                    $params[] = $matches[$paramIndex] ?? null;
                    $paramIndex++;
                }

                // Handle optional parameters
                foreach ($route['optionalParams'] as $name) {
                    $params[] = $matches[$paramIndex] ?? null;
                    $paramIndex++;
                }

                // Call the route handler with matched parameters
                return call_user_func_array($route['handler'], $params);
            }
        }

        // Handle not found
        if ($this->notFoundHandler) {
            return call_user_func($this->notFoundHandler);
        } else {
            http_response_code(404);
            echo "404 Not Found";
        }
    }

    /**
     * Convert route path to regex pattern
     * 
     * @param string $path
     * @return string
     */
    private function convertPathToRegex($path) {
        // Replace {param?} with optional regex capture groups
        $regex = preg_replace_callback('/\{([^}]+)\?}/', function($matches) {
            return '?([^/]*)';
        }, $path);

        // Replace {param} with required regex capture groups
        $regex = preg_replace_callback('/\{([^}]+)\}/', function($matches) {
            return '([^/]+)';
        }, $regex);
        
        return '#^' . $regex . '$#';
    }

    /**
     * Extract required parameter names from the path
     * 
     * @param string $path
     * @return array
     */
    private function extractParams($path) {
        preg_match_all('/\{([^}]+)\}(?!\?)/', $path, $matches);
        return $matches[1];
    }

    /**
     * Extract optional parameter names from the path
     * 
     * @param string $path
     * @return array
     */
    private function extractOptionalParams($path) {
        preg_match_all('/\{([^}]+)\?}/', $path, $matches);
        return $matches[1];
    }
}