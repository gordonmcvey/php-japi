<?php

/**
 * Copyright 2025 Gordon McVey
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

use Docnet\JAPI\controller\RequestHandlerInterface;
use gordonmcvey\httpsupport\RequestInterface;
use gordonmcvey\httpsupport\ResponseInterface;

class CallStack implements RequestHandlerInterface
{
    private RequestHandlerInterface $entryPoint;

    public function __construct(private readonly RequestHandlerInterface $root)
    {
        $this->entryPoint = $this->root;
    }

    /**
     * Add additional middleware to the call stack
     */
    public function add(MiddlewareInterface $newMiddleware): self
    {
        $this->entryPoint = new Slot($newMiddleware, $this->entryPoint);

        return $this;
    }

    /**
     * Clear the middleware stack with the exception of the root item
     */
    public function reset(): self
    {
        $this->entryPoint = $this->root;
        return $this;
    }

    /**
     * Replace any existing middleware in the call stack with the provided middleware
     */
    public function replaceWith(MiddlewareInterface $middleware): self
    {
        return $this->reset()->add($middleware);
    }

    public function fromProvider(MiddlewareProviderInterface $provider): self
    {
        foreach ($provider->getAllMiddleware() as $middleware) {
            $this->add($middleware);
        }

        return $this;
    }

    public function dispatch(RequestInterface $request): ?ResponseInterface
    {
        return $this->entryPoint->dispatch($request);
    }
}
