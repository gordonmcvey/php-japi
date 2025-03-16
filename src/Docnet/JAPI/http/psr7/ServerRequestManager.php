<?php

declare(strict_types=1);

namespace Docnet\JAPI\http\psr7;

use Psr\Http\Message\ServerRequestInterface;

class ServerRequestManager extends RequestManager
{
    private ?array $queryParams;

    private ?array $postParams;

    private ?array $serverParams;

    private ?array $cookieParams;

    private ?string $body;
    
    private ?array $parsedBody;

    public function __construct(private readonly ServerRequestInterface $request)
    {
    }

    public function queryParam(string $name, mixed $default = null): mixed
    {
        return $this->queryParams()[$name] ?? $default;
    }

    public function queryParams(): array
    {
        if (null === $this->queryParams) {
            $this->queryParams = $this->request->getQueryParams();
        }

        return $this->queryParams;
    }

    public function postParam(string $name, mixed $default = null): mixed
    {
        return $this->postParams()[$name] ?? $default;
    }

    public function postParams(): array
    {
        if (null === $this->postParams) {
            $this->postParams = $this->request->getQueryParams();
        }

        return $this->postParams;
    }

    public function cookieParam(string $name, mixed $default = null): mixed
    {
        return $this->cookieParams()[$name] ?? $default;
    }

    public function cookieParams(): array
    {
        if (null === $this->cookieParams) {
            $this->cookieParams = $this->request->getCookieParams();
        }
        return $this->cookieParams;
    }

    public function serverParam(string $name, mixed $default = null): mixed
    {
        return $this->serverParams[$name] ?? $default;
    }

    public function serverParams(): array
    {
        if (null === $this->serverParams) {
            $this->serverParams = $this->request->getServerParams();
        }

        return $this->serverParams;
    }

    public function bodyParam(string $name, mixed $default = null): mixed
    {
        return $this->bodyParams()[$name] ?? $default;
    }

    public function bodyParams(): array
    {
        if (null === $this->parsedBody) {
            $this->body = $this->request->getParsedBody();
        }

        return $this->parsedBody;
    }

    public function body(): ?string
    {
        if (null === $this->body)
        {
            $body = $this->request->getBody();
            $body->isSeekable() && $body->rewind();
            $this->body = $body->getContents();
        }

        return $this->body;
    }
}
