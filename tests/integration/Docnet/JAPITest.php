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

namespace Docnet\JAPI\test\integration;

use Docnet\JAPI;
use Docnet\JAPI\controller\RequestHandlerInterface;
use Docnet\JAPI\error\ErrorHandlerInterface;
use Docnet\JAPI\Exceptions\AccessDenied;
use Docnet\JAPI\Exceptions\Auth;
use Docnet\JAPI\Exceptions\Routing;
use Docnet\JAPI\middleware\CallStackFactory;
use Docnet\JAPI\middleware\MiddlewareInterface;
use Docnet\JAPI\middleware\MiddlewareProviderInterface;
use gordonmcvey\httpsupport\enum\statuscodes\ClientErrorCodes;
use gordonmcvey\httpsupport\RequestInterface;
use gordonmcvey\httpsupport\ResponseInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class JAPITest extends TestCase
{
    #[Test]
    public function itHandlesATypicalDispatchCycle(): void
    {
        $mockController = $this->createMock(RequestHandlerInterface::class);
        $mockRequest = $this->createMock(RequestInterface::class);
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockErrorHandler = $this->createMock(ErrorHandlerInterface::class);

        $mockController->expects($this->once())
            ->method("dispatch")
            ->with($mockRequest)
            ->willReturn($mockResponse)
        ;

        $mockErrorHandler->expects($this->never())
            ->method("handle")
        ;

        // Use a partial mock so we can check behaviour via the mocked output methods
        $japi = $this->getMockBuilder(JAPI::class)
            ->setConstructorArgs([new CallStackFactory(), $mockErrorHandler])
            ->onlyMethods(['sendResponse'])
            ->getMock()
        ;
 
        // JAPI expectatations
        $japi->expects($this->once())
            ->method("sendResponse")
            ->with($mockResponse)
        ;

        $japi->bootstrap($mockController, $mockRequest);
    }

    #[Test]
    public function itHandlesATypicalDispatchCycleWithFactoryFunction(): void
    {
        $mockController = $this->createMock(RequestHandlerInterface::class);
        $mockRequest = $this->createMock(RequestInterface::class);
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockErrorHandler = $this->createMock(ErrorHandlerInterface::class);

        $mockController->expects($this->once())
            ->method("dispatch")
            ->with($mockRequest)
            ->willReturn($mockResponse)
        ;

        $mockErrorHandler->expects($this->never())
            ->method("handle")
        ;

        // Use a partial mock so we can check behaviour via the mocked output methods
        $japi = $this->getMockBuilder(JAPI::class)
            ->setConstructorArgs([new CallStackFactory(), $mockErrorHandler])
            ->onlyMethods(['sendResponse'])
            ->getMock()
        ;
 
        // JAPI expectatations
        $japi->expects($this->once())
            ->method("sendResponse")
            ->with($mockResponse)
        ;

        $japi->bootstrap(fn() => $mockController, $mockRequest);
    }

    #[Test]
    public function itHandlesATypicalDispatchCycleWithFactoryObject(): void
    {
        $mockController = $this->createMock(RequestHandlerInterface::class);
        $mockRequest = $this->createMock(RequestInterface::class);
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockErrorHandler = $this->createMock(ErrorHandlerInterface::class);

        $mockController->expects($this->once())
            ->method("dispatch")
            ->with($mockRequest)
            ->willReturn($mockResponse)
        ;

        $mockErrorHandler->expects($this->never())
            ->method("handle")
        ;

        // Use a partial mock so we can check behaviour via the mocked output methods
        $japi = $this->getMockBuilder(JAPI::class)
            ->setConstructorArgs([new CallStackFactory(), $mockErrorHandler])
            ->onlyMethods(['sendResponse'])
            ->getMock()
        ;
 
        // JAPI expectatations
        $japi->expects($this->once())
            ->method("sendResponse")
            ->with($mockResponse)
        ;

        $japi->bootstrap(
            new class($mockController) {
                public function __construct(private readonly RequestHandlerInterface $controller) {}
                public function __invoke(): RequestHandlerInterface {
                    return $this->controller;
                }
            },
            $mockRequest
        );
    }

    #[Test]
    public function itHandlesATypicalDispatchCycleWithGlobalMiddleware(): void
    {
        $mockController = $this->createMock(RequestHandlerInterface::class);
        $mockRequest = $this->createMock(RequestInterface::class);
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockErrorHandler = $this->createMock(ErrorHandlerInterface::class);

        $mockController->expects($this->once())
            ->method("dispatch")
            ->with($mockRequest)
            ->willReturn($mockResponse)
        ;

        $mockRequest->expects($this->once())
            ->method("setHeader")
            ->with("foo", "bar")
            ->willReturnSelf()
        ;

        $mockResponse->expects($this->once())
            ->method("setHeader")
            ->with("baz", "quux")
            ->willReturnSelf()
        ;

        $mockErrorHandler->expects($this->never())
            ->method("handle")
        ;

        $middleware = new class implements MiddlewareInterface
        {
            public function handle(RequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
                $request->setHeader("foo", "bar");
                $response = $handler->dispatch($request);
                $response->setHeader("baz", "quux");

                return $response;
            }
        };

        // Use a partial mock so we can check behaviour via the mocked output methods
        $japi = $this->getMockBuilder(JAPI::class)
            ->setConstructorArgs([new CallStackFactory(), $mockErrorHandler])
            ->onlyMethods(['sendResponse'])
            ->getMock()
        ;
 
        // JAPI expectatations
        $japi->expects($this->once())
            ->method("sendResponse")
            ->with($mockResponse)
        ;


        $japi->addMiddleware($middleware)->bootstrap($mockController, $mockRequest);
    }

    #[Test]
    public function itHandlesATypicalDispatchCycleWithControllerMiddleware(): void
    {
        $mockController = $this->createMockForIntersectionOfInterfaces([
            RequestHandlerInterface::class,
            MiddlewareProviderInterface::class,
        ]);
        $mockRequest = $this->createMock(RequestInterface::class);
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockErrorHandler = $this->createMock(ErrorHandlerInterface::class);

        $mockRequest->expects($this->once())
            ->method("setHeader")
            ->with("foo", "bar")
            ->willReturnSelf()
        ;

        $mockResponse->expects($this->once())
            ->method("setHeader")
            ->with("baz", "quux")
            ->willReturnSelf()
        ;

        $middleware = new class implements MiddlewareInterface
        {
            public function handle(RequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
                $request->setHeader("foo", "bar");
                $response = $handler->dispatch($request);
                $response->setHeader("baz", "quux");

                return $response;
            }
        };

        $mockController->expects($this->once())
            ->method("getAllMiddleware")
            ->willReturn([$middleware])
        ;

        $mockController->expects($this->once())
            ->method("dispatch")
            ->with($mockRequest)
            ->willReturn($mockResponse)
        ;

        $mockErrorHandler->expects($this->never())
            ->method("handle")
        ;

        // Use a partial mock so we can check behaviour via the mocked output methods
        $japi = $this->getMockBuilder(JAPI::class)
            ->setConstructorArgs([new CallStackFactory(), $mockErrorHandler])
            ->onlyMethods(['sendResponse'])
            ->getMock()
        ;
 
        // JAPI expectatations
        $japi->expects($this->once())
            ->method("sendResponse")
            ->with($mockResponse)
        ;

        $japi->bootstrap($mockController, $mockRequest);
    }

    #[Test]
    public function itHandlesABootStrappingError(): void
    {
        $mockRequest = $this->createMock(RequestInterface::class);
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockErrorHandler = $this->createMock(ErrorHandlerInterface::class);

        // Use a partial mock so we can check behaviour via the mocked output methods
        $japi = $this->getMockBuilder(JAPI::class)
            ->setConstructorArgs([new CallStackFactory(), $mockErrorHandler])
            ->onlyMethods(['sendResponse'])
            ->getMock()
        ;
 
        // JAPI expectatations
        $japi->expects($this->once())
            ->method("sendResponse")
            ->with($mockResponse)
        ;

        $mockErrorHandler->expects($this->once())
            ->method("handle")
            ->with($this->isInstanceOf(\Exception::class))
            ->willReturn($mockResponse)
        ;

        $japi->bootstrap(fn() => "Hello", $mockRequest);
    }

    #[Test]
    public function itHandlesARoutingError(): void
    {
        $mockRequest = $this->createMock(RequestInterface::class);
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockErrorHandler = $this->createMock(ErrorHandlerInterface::class);

        // Use a partial mock so we can check behaviour via the mocked output methods
        $japi = $this->getMockBuilder(JAPI::class)
            ->setConstructorArgs([new CallStackFactory(), $mockErrorHandler,])
            ->onlyMethods(['sendResponse'])
            ->getMock()
        ;
 
        // JAPI expectatations
        $japi->expects($this->once())
            ->method("sendResponse")
            ->with($mockResponse)
        ;

        $mockErrorHandler->expects($this->once())
            ->method("handle")
            ->with($this->isInstanceOf(Routing::class))
            ->willReturn($mockResponse)
        ;

        $japi->bootstrap(fn() => throw new Routing(), $mockRequest);
    }

    #[Test]
    public function itHandlesAuthError(): void
    {
        $mockController = $this->createMock(RequestHandlerInterface::class);
        $mockRequest = $this->createMock(RequestInterface::class);
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockErrorHandler = $this->createMock(ErrorHandlerInterface::class);

        $mockController->expects($this->once())
            ->method("dispatch")
            ->with($mockRequest)
            ->willThrowException(new Auth())
        ;

        // Use a partial mock so we can check behaviour via the mocked output methods
        $japi = $this->getMockBuilder(JAPI::class)
            ->setConstructorArgs([new CallStackFactory(), $mockErrorHandler])
            ->onlyMethods(['sendResponse'])
            ->getMock()
        ;
 
        // JAPI expectatations
        $japi->expects($this->once())
            ->method("sendResponse")
            ->with($mockResponse)
        ;

        $mockErrorHandler->expects($this->once())
            ->method("handle")
            ->with($this->isInstanceOf(Auth::class))
            ->willReturn($mockResponse)
        ;

        $japi->bootstrap($mockController, $mockRequest);
    }

    #[Test]
    public function itHandlesAccessDeniedError(): void
    {
        $mockController = $this->createMock(RequestHandlerInterface::class);
        $mockRequest = $this->createMock(RequestInterface::class);
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockErrorHandler = $this->createMock(ErrorHandlerInterface::class);

        $mockController->expects($this->once())
            ->method("dispatch")
            ->with($mockRequest)
            ->willThrowException(new AccessDenied())
        ;

        // Use a partial mock so we can check behaviour via the mocked output methods
        $japi = $this->getMockBuilder(JAPI::class)
            ->setConstructorArgs([new CallStackFactory(), $mockErrorHandler])
            ->onlyMethods(['sendResponse'])
            ->getMock()
        ;
 
        // JAPI expectatations
        $japi->expects($this->once())
            ->method("sendResponse")
            ->with($mockResponse)
        ;

        $mockErrorHandler->expects($this->once())
            ->method("handle")
            ->with($this->isInstanceOf(AccessDenied::class))
            ->willReturn($mockResponse)
        ;

        $japi->bootstrap($mockController, $mockRequest);
    }

    #[Test]
    public function itHandlesGeneralError(): void
    {
        $mockController = $this->createMock(RequestHandlerInterface::class);
        $mockRequest = $this->createMock(RequestInterface::class);
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockErrorHandler = $this->createMock(ErrorHandlerInterface::class);

        $mockController->expects($this->once())
            ->method("dispatch")
            ->with($mockRequest)
            ->willThrowException(new \RuntimeException(code: 12345))
        ;

        // Use a partial mock so we can check behaviour via the mocked output methods
        $japi = $this->getMockBuilder(JAPI::class)
            ->setConstructorArgs([new CallStackFactory(), $mockErrorHandler])
            ->onlyMethods(['sendResponse'])
            ->getMock()
        ;
 
        // JAPI expectatations
        $japi->expects($this->once())
            ->method("sendResponse")
            ->with($mockResponse)
        ;

        $mockErrorHandler->expects($this->once())
            ->method("handle")
            ->with($this->isInstanceOf(\RuntimeException::class))
            ->willReturn($mockResponse)
        ;

        $japi->bootstrap($mockController, $mockRequest);
    }

    #[Test]
    public function itHandlesGeneralErrorWithValidErrorCode(): void
    {
        $mockController = $this->createMock(RequestHandlerInterface::class);
        $mockRequest = $this->createMock(RequestInterface::class);
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockErrorHandler = $this->createMock(ErrorHandlerInterface::class);

        $mockController->expects($this->once())
            ->method("dispatch")
            ->with($mockRequest)
            ->willThrowException(new \RuntimeException(code: ClientErrorCodes::UNAVAILABLE_FOR_LEGAL_REASONS->value))
        ;

        // Use a partial mock so we can check behaviour via the mocked output methods
        $japi = $this->getMockBuilder(JAPI::class)
            ->setConstructorArgs([new CallStackFactory(), $mockErrorHandler])
            ->onlyMethods(['sendResponse'])
            ->getMock()
        ;
 
        // JAPI expectatations
        $japi->expects($this->once())
            ->method("sendResponse")
            ->with($mockResponse)
        ;

        $mockErrorHandler->expects($this->once())
            ->method("handle")
            ->with($this->isInstanceOf(\RuntimeException::class))
            ->willReturn($mockResponse)
        ;

        $japi->bootstrap($mockController, $mockRequest);
    }
}
