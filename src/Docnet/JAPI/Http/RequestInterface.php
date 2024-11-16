<?php

declare(strict_types=1);

namespace Docnet\JAPI\Http;

use Docnet\JAPI\Http\Enum\Verbs;

interface RequestInterface
{
    /**
     * @return array<string, mixed>
     */
    public function headers(): array;

    public function header(string $name, mixed $default = null): mixed;

    public function verb(): Verbs;

    public function isPost(): bool;

    public function param(string $name, mixed $default = null): mixed;

    public function queryParam(string $name, mixed $default = null): mixed;
    
    public function postParam(string $name, mixed $default = null): mixed;

    public function cookieParam(string $name, mixed $default = null): mixed;

    public function serverParam(string $name, mixed $default = null): mixed;

    /**
     * @return array<string, array{
     *     name: string,
     *     type: string,
     *     size: non-negative-int,
     *     tmp_name: string,
     *     error_code: non-negative-int
     * }>
     */
    public function uploadedFiles(): array;

    /**
     * @return ?array{
     *     name: string,
     *     type: string,
     *     size: non-negative-int,
     *     tmp_name: string,
     *     error_code: non-negative-int
     * }
     */
    public function uploadedFile(string $name): ?array;

    public function body(): ?string;
}
