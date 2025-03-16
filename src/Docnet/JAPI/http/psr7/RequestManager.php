<?php

declare(strict_types=1);

namespace Docnet\JAPI\http\psr7;

use gordonmcvey\httpsupport\enum\Verbs;
use Psr\Http\Message\RequestInterface;

class RequestManager
{
    public function __construct(private readonly RequestInterface $request)
    {
    }

    /**
     * @return string<string, array<array-key, string>>
     */

    public function header(string $name, mixed $default = null): mixed
    {
        return $this->headers()[$name] ?? $default;
    }

    public function headers(): array
    {
        return $this->request->getHeaders();
    }

    public function setHeader(string $name, mixed $value): self
    {
        $this->request->withHeader($name, $value);
        return $this;
    }

    public function contentType(): ?string
    {
        return $this->request->getHeader('Content-Type')[0] ?? null;
    }

    public function contentLength(): ?int
    {
        return $this->request->getBody()->getSize();
    }

    public function version(): string
    {
        return $this->request->getProtocolVersion();
    }

    public function verb(): Verbs
    {
        return Verbs::from($this->request->getMethod());
    }

    public function uri(): string
    {
        return $this->request->getRequestTarget();
    }
}
