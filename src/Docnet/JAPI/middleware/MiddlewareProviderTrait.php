<?php

/**
 * Copyright © 2025 Gordon McVey
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

declare(strict_types=1);

namespace Docnet\JAPI\middleware;

trait MiddlewareProviderTrait
{
    /**
     * @var array<array-key, MiddlewareInterface>
     */
    private array $middleware = [];

    public function addMiddleware(MiddlewareInterface $newMiddleware): MiddlewareProviderInterface
    {
        $this->middleware[] = $newMiddleware;
        return $this;
    }

    public function resetMiddleware(): MiddlewareProviderInterface
    {
        $this->middleware = [];
        return $this;
    }

    public function replaceMiddlewareWith(MiddlewareInterface $middleware): MiddlewareProviderInterface
    {
        return $this->resetMiddleware()->addMiddleware($middleware);
    }

    /**
     * @return array<array-key, MiddlewareInterface>
     */
    public function getAllMiddleware(): array
    {
        return $this->middleware;
    }
}
