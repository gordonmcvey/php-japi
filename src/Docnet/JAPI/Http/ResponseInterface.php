<?php

declare(strict_types=1);

namespace Docnet\JAPI\Http;

interface ResponseInterface
{
    public function setHeader(string $key, string $value): self;

    public function header(string $key): ?string;

    /**
     * @return array<string, string>
     */
    public function headers(): array;

    public function sendHeaders(): self;

    public function body(): string;
}
