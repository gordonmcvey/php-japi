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

namespace Docnet\JAPI\test\unit;

use Docnet\JAPI;
use Docnet\JAPI\controller\RequestHandlerInterface;
use Docnet\JAPI\Exceptions\AccessDenied;
use Docnet\JAPI\Exceptions\Auth;
use Docnet\JAPI\Exceptions\Routing;
use Docnet\JAPI\middleware\CallStack;
use Docnet\JAPI\middleware\CallStackFactory;
use gordonmcvey\httpsupport\enum\factory\StatusCodeFactory;
use gordonmcvey\httpsupport\enum\statuscodes\ClientErrorCodes;
use gordonmcvey\httpsupport\enum\statuscodes\ServerErrorCodes;
use gordonmcvey\httpsupport\RequestInterface;
use gordonmcvey\httpsupport\ResponseInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class JAPITest extends TestCase
{
    #[Test]
    public function itHandlesATypicalDispatchCycle(): void
    {
        // Can't really mock this but it doesn't matter too much as it's a very simple factory
        $mockStatusCodeFactory = new StatusCodeFactory();

        $mockCallStackFactory = $this->createMock(CallStackFactory::class);
        $mockCallStack = $this->createMock(CallStack::class);
        $mockController = $this->createMock(RequestHandlerInterface::class);
        $mockRequest = $this->createMock(RequestInterface::class);
        $mockResponse = $this->createMock(ResponseInterface::class);

        $mockCallStackFactory->expects($this->once())
            ->method("make")
            ->with($mockController)
            ->willReturn($mockCallStack)
        ;

        $mockController->expects($this->once())
            ->method("dispatch")
            ->with($mockRequest)
            ->willReturn($mockResponse)
        ;

        $mockCallStack->expects($this->once())
            ->method("dispatch")
            ->with($mockRequest)
            ->willReturn($mockController->dispatch($mockRequest));
        ;

        // Use a partial mock so we can check behaviour via the mocked output methods
        $japi = $this->getMockBuilder(JAPI::class)
            ->setConstructorArgs([$mockStatusCodeFactory, $mockCallStackFactory])
            ->onlyMethods(['sendResponse', 'jsonError'])
            ->getMock()
        ;

        // JAPI expectatations
        $japi->expects($this->once())
            ->method("sendResponse")
            ->with($mockResponse)
        ;

        $japi->expects($this->never())
            ->method("jsonError")
        ;

        $japi->bootstrap($mockController, $mockRequest);
    }

    #[Test]
    public function itHandlesATypicalDispatchCycleWithControllerFactoryFunction(): void
    {
        // Can't really mock this but it doesn't matter too much as it's a very simple factory
        $mockStatusCodeFactory = new StatusCodeFactory();

        $mockCallStackFactory = $this->createMock(CallStackFactory::class);
        $mockCallStack = $this->createMock(CallStack::class);
        $mockController = $this->createMock(RequestHandlerInterface::class);
        $mockRequest = $this->createMock(RequestInterface::class);
        $mockResponse = $this->createMock(ResponseInterface::class);

        $mockCallStackFactory->expects($this->once())
            ->method("make")
            ->with($mockController)
            ->willReturn($mockCallStack)
        ;

        $mockController->expects($this->once())
            ->method("dispatch")
            ->with($mockRequest)
            ->willReturn($mockResponse)
        ;

        $mockCallStack->expects($this->once())
            ->method("dispatch")
            ->with($mockRequest)
            ->willReturn($mockController->dispatch($mockRequest));
        ;

        // Use a partial mock so we can check behaviour via the mocked output methods
        $japi = $this->getMockBuilder(JAPI::class)
            ->setConstructorArgs([$mockStatusCodeFactory, $mockCallStackFactory])
            ->onlyMethods(['sendResponse', 'jsonError'])
            ->getMock()
        ;

        // JAPI expectatations
        $japi->expects($this->once())
            ->method("sendResponse")
            ->with($mockResponse)
        ;

        $japi->expects($this->never())
            ->method("jsonError")
        ;

        $japi->bootstrap(fn() => $mockController, $mockRequest);
    }

    #[Test]
    public function itHandlesATypicalDispatchCycleWithControllerFactoryObject(): void
    {
        // Can't really mock this but it doesn't matter too much as it's a very simple factory
        $mockStatusCodeFactory = new StatusCodeFactory();

        $mockCallStackFactory = $this->createMock(CallStackFactory::class);
        $mockCallStack = $this->createMock(CallStack::class);
        $mockController = $this->createMock(RequestHandlerInterface::class);
        $mockRequest = $this->createMock(RequestInterface::class);
        $mockResponse = $this->createMock(ResponseInterface::class);

        $mockCallStackFactory->expects($this->once())
            ->method("make")
            ->with($mockController)
            ->willReturn($mockCallStack)
        ;

        $mockController->expects($this->once())
            ->method("dispatch")
            ->with($mockRequest)
            ->willReturn($mockResponse)
        ;

        $mockCallStack->expects($this->once())
            ->method("dispatch")
            ->with($mockRequest)
            ->willReturn($mockController->dispatch($mockRequest));
        ;

        // Use a partial mock so we can check behaviour via the mocked output methods
        $japi = $this->getMockBuilder(JAPI::class)
            ->setConstructorArgs([$mockStatusCodeFactory, $mockCallStackFactory])
            ->onlyMethods(['sendResponse', 'jsonError'])
            ->getMock()
        ;

        // JAPI expectatations
        $japi->expects($this->once())
            ->method("sendResponse")
            ->with($mockResponse)
        ;

        $japi->expects($this->never())
            ->method("jsonError")
        ;

        $japi->bootstrap(
            new class($mockController) {
                public function __construct(private readonly RequestHandlerInterface $controller) {}
                public function __invoke(): RequestHandlerInterface {
                    return $this->controller;
                }
            },
            $mockRequest,
        );
    }

    #[Test]
    public function itHandlesABootstrappingError(): void
    {
        // Can't really mock this but it doesn't matter too much as it's a very simple factory
        $mockStatusCodeFactory = new StatusCodeFactory();

        $mockCallStackFactory = $this->createMock(CallStackFactory::class);
        $mockRequest = $this->createMock(RequestInterface::class);

        // Use a partial mock so we can check behaviour via the mocked output methods
        $japi = $this->getMockBuilder(JAPI::class)
            ->setConstructorArgs([$mockStatusCodeFactory, $mockCallStackFactory])
            ->onlyMethods(['sendResponse', 'jsonError'])
            ->getMock()
        ;

        // JAPI expectatations
        $japi->expects($this->never())
            ->method("sendResponse")
        ;

        $japi->expects($this->once())
            ->method("jsonError")
            ->with($this->isInstanceOf(\Exception::class), ServerErrorCodes::INTERNAL_SERVER_ERROR)
        ;

        $japi->bootstrap(fn() => "Hello" , $mockRequest);        
    }

    #[Test]
    public function itHandlesARoutingError(): void
    {
        // Can't really mock this but it doesn't matter too much as it's a very simple factory
        $mockStatusCodeFactory = new StatusCodeFactory();

        $mockCallStackFactory = $this->createMock(CallStackFactory::class);
        $mockRequest = $this->createMock(RequestInterface::class);

        // Use a partial mock so we can check behaviour via the mocked output methods
        $japi = $this->getMockBuilder(JAPI::class)
            ->setConstructorArgs([$mockStatusCodeFactory, $mockCallStackFactory])
            ->onlyMethods(['sendResponse', 'jsonError'])
            ->getMock()
        ;

        // JAPI expectatations
        $japi->expects($this->never())
            ->method("sendResponse")
        ;

        $japi->expects($this->once())
            ->method("jsonError")
            ->with($this->isInstanceOf(Routing::class), ClientErrorCodes::NOT_FOUND)
        ;

        $japi->bootstrap(fn() => throw new Routing() , $mockRequest);        
    }

    #[Test]
    public function itHandlesAuthError(): void
    {
        // Can't really mock this but it doesn't matter too much as it's a very simple factory
        $mockStatusCodeFactory = new StatusCodeFactory();

        $mockCallStackFactory = $this->createMock(CallStackFactory::class);
        $mockCallStack = $this->createMock(CallStack::class);
        $mockController = $this->createMock(RequestHandlerInterface::class);
        $mockRequest = $this->createMock(RequestInterface::class);

        $mockCallStackFactory->expects($this->once())
            ->method("make")
            ->with($mockController)
            ->willReturn($mockCallStack)
        ;

        $mockCallStack->expects($this->once())
            ->method("dispatch")
            ->with($mockRequest)
            ->willThrowException(new Auth())
        ;

        // Use a partial mock so we can check behaviour via the mocked output methods
        $japi = $this->getMockBuilder(JAPI::class)
            ->setConstructorArgs([$mockStatusCodeFactory, $mockCallStackFactory])
            ->onlyMethods(['sendResponse', 'jsonError'])
            ->getMock()
        ;

        // JAPI expectatations
        $japi->expects($this->never())
            ->method("sendResponse")
        ;

        $japi->expects($this->once())
            ->method("jsonError")
            ->with($this->isInstanceOf(Auth::class), ClientErrorCodes::UNAUTHORIZED)
        ;

        $japi->bootstrap($mockController, $mockRequest);
    }

    #[Test]
    public function itHandlesAccessDeniedError(): void
    {
        // Can't really mock this but it doesn't matter too much as it's a very simple factory
        $mockStatusCodeFactory = new StatusCodeFactory();

        $mockCallStackFactory = $this->createMock(CallStackFactory::class);
        $mockCallStack = $this->createMock(CallStack::class);
        $mockController = $this->createMock(RequestHandlerInterface::class);
        $mockRequest = $this->createMock(RequestInterface::class);

        $mockCallStackFactory->expects($this->once())
            ->method("make")
            ->with($mockController)
            ->willReturn($mockCallStack)
        ;

        $mockCallStack->expects($this->once())
            ->method("dispatch")
            ->with($mockRequest)
            ->willThrowException(new AccessDenied())
        ;

        // Use a partial mock so we can check behaviour via the mocked output methods
        $japi = $this->getMockBuilder(JAPI::class)
            ->setConstructorArgs([$mockStatusCodeFactory, $mockCallStackFactory])
            ->onlyMethods(['sendResponse', 'jsonError'])
            ->getMock()
        ;

        // JAPI expectatations
        $japi->expects($this->never())
            ->method("sendResponse")
        ;

        $japi->expects($this->once())
            ->method("jsonError")
            ->with($this->isInstanceOf(AccessDenied::class), ClientErrorCodes::FORBIDDEN)
        ;

        $japi->bootstrap($mockController, $mockRequest);
    }

    #[Test]
    public function itHandlesGeneralError(): void
    {
        // Can't really mock this but it doesn't matter too much as it's a very simple factory
        $mockStatusCodeFactory = new StatusCodeFactory();

        $mockCallStackFactory = $this->createMock(CallStackFactory::class);
        $mockCallStack = $this->createMock(CallStack::class);
        $mockController = $this->createMock(RequestHandlerInterface::class);
        $mockRequest = $this->createMock(RequestInterface::class);

        $mockCallStackFactory->expects($this->once())
            ->method("make")
            ->with($mockController)
            ->willReturn($mockCallStack)
        ;

        $mockCallStack->expects($this->once())
            ->method("dispatch")
            ->with($mockRequest)
            ->willThrowException(new \RuntimeException(code: 12345))
        ;

        // Use a partial mock so we can check behaviour via the mocked output methods
        $japi = $this->getMockBuilder(JAPI::class)
            ->setConstructorArgs([$mockStatusCodeFactory, $mockCallStackFactory])
            ->onlyMethods(['sendResponse', 'jsonError'])
            ->getMock()
        ;

        // JAPI expectatations
        $japi->expects($this->never())
            ->method("sendResponse")
        ;

        $japi->expects($this->once())
            ->method("jsonError")
            ->with($this->isInstanceOf(\RuntimeException::class), ServerErrorCodes::INTERNAL_SERVER_ERROR)
        ;

        $japi->bootstrap($mockController, $mockRequest);
    }

    #[Test]
    public function itHandlesGeneralErrorWithValidErrorCode(): void
    {
        // Can't really mock this but it doesn't matter too much as it's a very simple factory
        $mockStatusCodeFactory = new StatusCodeFactory();

        $mockCallStackFactory = $this->createMock(CallStackFactory::class);
        $mockCallStack = $this->createMock(CallStack::class);
        $mockController = $this->createMock(RequestHandlerInterface::class);
        $mockRequest = $this->createMock(RequestInterface::class);

        $mockCallStackFactory->expects($this->once())
            ->method("make")
            ->with($mockController)
            ->willReturn($mockCallStack)
        ;

        $mockCallStack->expects($this->once())
            ->method("dispatch")
            ->with($mockRequest)
            ->willThrowException(new \RuntimeException(code: ClientErrorCodes::UNAVAILABLE_FOR_LEGAL_REASONS->value))
        ;

        // Use a partial mock so we can check behaviour via the mocked output methods
        $japi = $this->getMockBuilder(JAPI::class)
            ->setConstructorArgs([$mockStatusCodeFactory, $mockCallStackFactory])
            ->onlyMethods(['sendResponse', 'jsonError'])
            ->getMock()
        ;

        // JAPI expectatations
        $japi->expects($this->never())
            ->method("sendResponse")
        ;

        $japi->expects($this->once())
            ->method("jsonError")
            ->with($this->isInstanceOf(\RuntimeException::class), ClientErrorCodes::UNAVAILABLE_FOR_LEGAL_REASONS)
        ;

        $japi->bootstrap($mockController, $mockRequest);
    }

    #[Test]
    public function itLogsErrorsIfGivenALogger(): void
    {
        // Can't really mock this but it doesn't matter too much as it's a very simple factory
        $mockStatusCodeFactory = new StatusCodeFactory();

        $mockCallStackFactory = $this->createMock(CallStackFactory::class);
        $mockRequest = $this->createMock(RequestInterface::class);
        $mockLogger = $this->createMock(LoggerInterface::class);

        // Use a partial mock so we can check behaviour via the mocked output methods
        $japi = $this->getMockBuilder(JAPI::class)
            ->setConstructorArgs([$mockStatusCodeFactory, $mockCallStackFactory])
            ->onlyMethods(['sendResponse'])
            ->getMock()
        ;

        $mockLogger->expects($this->once())
            ->method("error")
            ->with("[JAPI] [500] Error: Exception: Unable to bootstrap")
        ;

        $japi->setLogger($mockLogger);
        $japi->bootstrap(fn() => "Hello" , $mockRequest);        
    }
}
