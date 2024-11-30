<?php
namespace Df3g\Router;

class Request {
    private $method;
    private $uri;
    private $params;
    private $query;
    private $headers;
    private $body;

    public function __construct($method, $uri, $params = [], $query = null, $headers = null, $body = null) {
        $this->method = strtoupper($method);
        $this->uri = $uri;
        $this->params = $params;
        
        // Parse query string if not provided
        if ($query === null) {
            parse_str(parse_url($uri, PHP_URL_QUERY) ?? '', $this->query);
        } else {
            $this->query = $query;
        }

        // Get headers if not provided
        if ($headers === null) {
            $this->headers = $this->getRequestHeaders();
        } else {
            // Normalize header keys to lowercase
            $this->headers = array_change_key_case($headers, CASE_LOWER);
        }

        // Get request body if not provided
        if ($body === null) {
            $this->body = $this->getRequestBody();
        } else {
            $this->body = $body;
        }
    }

    public function getMethod() {
        return $this->method;
    }

    public function getUri() {
        return $this->uri;
    }

    public function getParams() {
        return $this->params;
    }

    public function getParam($name, $default = null) {
        return array_key_exists($name, $this->params) ? $this->params[$name] : $default;
    }

    public function getQuery() {
        return $this->query;
    }

    public function getQueryParam($name, $default = null) {
        return $this->query[$name] ?? $default;
    }

    public function getHeaders() {
        return $this->headers;
    }

    public function getHeader($name, $default = null) {
        $name = strtolower($name);
        return $this->headers[$name] ?? $default;
    }

    public function getBody() {
        return $this->body;
    }

    public function getJson() {
        if ($this->isJson()) {
            return json_decode($this->body, true);
        }
        return null;
    }

    public function isJson() {
        $contentType = $this->getHeader('content-type');
        return $contentType && strpos($contentType, 'application/json') !== false;
    }

    private function getRequestHeaders() {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $header = str_replace('_', '-', strtolower(substr($key, 5)));
                $headers[$header] = $value;
            } elseif (in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH'])) {
                $header = str_replace('_', '-', strtolower($key));
                $headers[$header] = $value;
            }
        }
        return $headers;
    }

    private function getRequestBody() {
        $body = file_get_contents('php://input');
        return $body ?: null;
    }
}