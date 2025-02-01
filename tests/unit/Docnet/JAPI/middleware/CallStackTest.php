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

namespace Docnet\JAPI\test\unit\middleware;

use Docnet\JAPI\controller\RequestHandlerInterface;
use Docnet\JAPI\middleware\CallStack;
use Docnet\JAPI\middleware\MiddlewareInterface;
use Docnet\JAPI\middleware\MiddlewareProviderInterface;
use gordonmcvey\httpsupport\RequestInterface;
use gordonmcvey\httpsupport\ResponseInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class CallStackTest extends TestCase
{
    #[Test]
    public function itRunsAController(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $controller = $this->createMock(RequestHandlerInterface::class);

        $controller->expects($this->once())
            ->method("dispatch")
            ->with($request)
            ->willReturn($response);
        
        $callStack = new CallStack($controller);

        $this->assertSame($response, $callStack->dispatch($request));
    }

    #[Test]
    public function itRunsAControllerWithMiddleware(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $controller = $this->createMock(RequestHandlerInterface::class);
        $middleware = $this->createMock(MiddlewareInterface::class);

        $middleware->expects($this->once())
            ->method("handle")
            ->with($request, $controller)
            ->willReturnCallback(fn(RequestInterface $request): ?ResponseInterface => $controller->dispatch($request));

        $controller->expects($this->once())
            ->method("dispatch")
            ->with($request)
            ->willReturn($response);

        $callStack = new CallStack($controller);
        $callStack->add($middleware);

        $this->assertSame($response, $callStack->dispatch($request));
    }

    #[Test]
    public function itAllowsResetting(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $controller = $this->createMock(RequestHandlerInterface::class);
        $middleware = $this->createMock(MiddlewareInterface::class);

        $middleware->expects($this->never())
            ->method("handle");

        $controller->expects($this->once())
            ->method("dispatch")
            ->with($request)
            ->willReturn($response);

        $callStack = new CallStack($controller);
        $callStack->add($middleware)
            ->reset();

        $this->assertSame($response, $callStack->dispatch($request));
    }

    #[Test]
    public function itAllowsReplacingMiddleware(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $controller = $this->createMock(RequestHandlerInterface::class);
        $removedMiddleware = $this->createMock(MiddlewareInterface::class);
        $replacingMiddleware = $this->createMock(MiddlewareInterface::class);

        $removedMiddleware->expects($this->never())
            ->method("handle");

        $replacingMiddleware->expects($this->once())
            ->method("handle")
            ->with($request, $controller)
            ->willReturnCallback(fn(RequestInterface $request): ?ResponseInterface => $controller->dispatch($request));

        $controller->expects($this->once())
            ->method("dispatch")
            ->with($request)
            ->willReturn($response);

        $callStack = new CallStack($controller);
        $callStack->add($removedMiddleware)
            ->replaceWith($replacingMiddleware);

        $this->assertSame($response, $callStack->dispatch($request));

    }

    #[Test]
    public function itAllowsMiddlewareProviders(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $controller = $this->createMock(RequestHandlerInterface::class);
        $middleware = $this->createMock(MiddlewareInterface::class);
        $provider = $this->createMock(MiddlewareProviderInterface::class);

        $middleware->expects($this->once())
            ->method("handle")
            ->with($request, $controller)
            ->willReturnCallback(fn(RequestInterface $request): ?ResponseInterface => $controller->dispatch($request));

        $controller->expects($this->once())
            ->method("dispatch")
            ->with($request)
            ->willReturn($response);

        $provider->expects($this->once())
            ->method("getAllMiddleware")
            ->willReturn([$middleware]);

        $callStack = new CallStack($controller);
        $callStack->fromProvider($provider);

        $this->assertSame($response, $callStack->dispatch($request));
    }
}
