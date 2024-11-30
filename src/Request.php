<?php
namespace Df3g\Router;

class Request {
    private array $params;
    private string $method;
    private string $uri;
    private array $queryParams;
    private array $body;

    public function __construct(
        string $method,
        string $uri,
        array $params = [],
        array $queryParams = [],
        array $body = []
    ) {
        $this->method = $method;
        $this->uri = $uri;
        $this->params = $params;
        $this->queryParams = $queryParams;
        $this->body = $body;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function getParam(string $name, $default = null)
    {
        return $this->params[$name] ?? $default;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    public function getBody(): array
    {
        return $this->body;
    }
} 