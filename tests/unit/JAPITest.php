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

namespace gordonmcvey\JAPI\test\unit;

use Docnet\JAPI\Exceptions\AccessDenied;
use Docnet\JAPI\Exceptions\Auth;
use Docnet\JAPI\Exceptions\Routing;
use Docnet\JAPI\interface\controller\RequestHandlerInterface;
use Docnet\JAPI\interface\error\ErrorHandlerInterface;
use Docnet\JAPI\JAPI;
use Docnet\JAPI\middleware\CallStack;
use Docnet\JAPI\middleware\CallStackFactory;
use gordonmcvey\httpsupport\enum\statuscodes\ClientErrorCodes;
use gordonmcvey\httpsupport\request\RequestInterface;
use gordonmcvey\httpsupport\response\ResponseInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class JAPITest extends TestCase
{
    /**
     * @throws Exception
     */
    #[Test]
    public function itHandlesATypicalDispatchCycle(): void
    {
        $mockCallStackFactory = $this->createMock(CallStackFactory::class);
        $mockCallStack = $this->createMock(CallStack::class);
        $mockController = $this->createMock(RequestHandlerInterface::class);
        $mockRequest = $this->createMock(RequestInterface::class);
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockErrorHandler = $this->createMock(ErrorHandlerInterface::class);

        // Use a partial mock so we can check behaviour via the mocked output methods
        $japi = $this->getMockBuilder(JAPI::class)
            ->setConstructorArgs([$mockCallStackFactory, $mockErrorHandler])
            ->onlyMethods(['sendResponse'])
            ->getMock();

        $mockCallStackFactory->expects($this->once())
            ->method("make")
            ->with($mockController, $japi)
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

        $mockErrorHandler->expects($this->never())
            ->method("handle")
        ;

        // JAPI expectations
        $japi->expects($this->once())
            ->method("sendResponse")
            ->with($mockResponse)
        ;

        $japi->bootstrap($mockController, $mockRequest);
    }

    /**
     * @throws Exception
     */
    #[Test]
    public function itHandlesATypicalDispatchCycleWithControllerFactoryFunction(): void
    {
        $mockCallStackFactory = $this->createMock(CallStackFactory::class);
        $mockCallStack = $this->createMock(CallStack::class);
        $mockController = $this->createMock(RequestHandlerInterface::class);
        $mockRequest = $this->createMock(RequestInterface::class);
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockErrorHandler = $this->createMock(ErrorHandlerInterface::class);

        // Use a partial mock so we can check behaviour via the mocked output methods
        $japi = $this->getMockBuilder(JAPI::class)
            ->setConstructorArgs([$mockCallStackFactory, $mockErrorHandler])
            ->onlyMethods(['sendResponse'])
            ->getMock();

        $mockCallStackFactory->expects($this->once())
            ->method("make")
            ->with($mockController, $japi)
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

        $mockErrorHandler->expects($this->never())
            ->method("handle")
        ;

        // JAPI expectations
        $japi->expects($this->once())
            ->method("sendResponse")
            ->with($mockResponse)
        ;

        $japi->bootstrap(fn() => $mockController, $mockRequest);
    }

    /**
     * @throws Exception
     */
    #[Test]
    public function itHandlesATypicalDispatchCycleWithControllerFactoryObject(): void
    {
        $mockCallStackFactory = $this->createMock(CallStackFactory::class);
        $mockCallStack = $this->createMock(CallStack::class);
        $mockController = $this->createMock(RequestHandlerInterface::class);
        $mockRequest = $this->createMock(RequestInterface::class);
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockErrorHandler = $this->createMock(ErrorHandlerInterface::class);

        // Use a partial mock so we can check behaviour via the mocked output methods
        $japi = $this->getMockBuilder(JAPI::class)
            ->setConstructorArgs([$mockCallStackFactory, $mockErrorHandler])
            ->onlyMethods(['sendResponse'])
            ->getMock();

        $mockCallStackFactory->expects($this->once())
            ->method("make")
            ->with($mockController, $japi)
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

        $mockErrorHandler->expects($this->never())
            ->method("handle")
        ;

        // JAPI expectations
        $japi->expects($this->once())
            ->method("sendResponse")
            ->with($mockResponse)
        ;

        $japi->bootstrap(
            new readonly class ($mockController) {
                public function __construct(private RequestHandlerInterface $controller)
                {
                }
                public function __invoke(): RequestHandlerInterface
                {
                    return $this->controller;
                }
            },
            $mockRequest,
        );
    }

    /**
     * @throws Exception
     */
    #[Test]
    public function itHandlesABootstrappingError(): void
    {
        $mockCallStackFactory = $this->createMock(CallStackFactory::class);
        $mockRequest = $this->createMock(RequestInterface::class);
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockErrorHandler = $this->createMock(ErrorHandlerInterface::class);

        $mockErrorHandler->expects($this->once())
            ->method("handle")
            ->with($this->isInstanceOf(\Exception::class))
            ->willReturn($mockResponse)
        ;

        // Use a partial mock so we can check behaviour via the mocked output methods
        $japi = $this->getMockBuilder(JAPI::class)
            ->setConstructorArgs([$mockCallStackFactory, $mockErrorHandler])
            ->onlyMethods(['sendResponse'])
            ->getMock()
        ;

        // JAPI expectations
        $japi->expects($this->once())
            ->method("sendResponse")
            ->with($mockResponse)
        ;

        $japi->bootstrap(fn() => "Hello", $mockRequest);
    }

    /**
     * @throws Exception
     */
    #[Test]
    public function itHandlesARoutingError(): void
    {
        $mockCallStackFactory = $this->createMock(CallStackFactory::class);
        $mockRequest = $this->createMock(RequestInterface::class);
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockErrorHandler = $this->createMock(ErrorHandlerInterface::class);

        $mockErrorHandler->expects($this->once())
            ->method("handle")
            ->with($this->isInstanceOf(Routing::class))
            ->willReturn($mockResponse)
        ;

        // Use a partial mock so we can check behaviour via the mocked output methods
        $japi = $this->getMockBuilder(JAPI::class)
            ->setConstructorArgs([$mockCallStackFactory, $mockErrorHandler])
            ->onlyMethods(['sendResponse'])
            ->getMock()
        ;

        // JAPI expectations
        $japi->expects($this->once())
            ->method("sendResponse")
            ->with($mockResponse);
        ;

        $japi->bootstrap(fn() => throw new Routing(), $mockRequest);
    }

    /**
     * @throws Exception
     */
    #[Test]
    public function itHandlesAuthError(): void
    {
        $mockCallStackFactory = $this->createMock(CallStackFactory::class);
        $mockCallStack = $this->createMock(CallStack::class);
        $mockController = $this->createMock(RequestHandlerInterface::class);
        $mockRequest = $this->createMock(RequestInterface::class);
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockErrorHandler = $this->createMock(ErrorHandlerInterface::class);

        // Use a partial mock so we can check behaviour via the mocked output methods
        $japi = $this->getMockBuilder(JAPI::class)
            ->setConstructorArgs([$mockCallStackFactory, $mockErrorHandler])
            ->onlyMethods(['sendResponse'])
            ->getMock();

        $mockCallStackFactory->expects($this->once())
            ->method("make")
            ->with($mockController, $japi)
            ->willReturn($mockCallStack)
        ;

        $mockCallStack->expects($this->once())
            ->method("dispatch")
            ->with($mockRequest)
            ->willThrowException(new Auth())
        ;

        $mockErrorHandler->expects($this->once())
            ->method("handle")
            ->with($this->isInstanceOf(Auth::class))
            ->willReturn($mockResponse)
        ;

        // JAPI expectations
        $japi->expects($this->once())
            ->method("sendResponse")
            ->with($mockResponse)
        ;

        $japi->bootstrap($mockController, $mockRequest);
    }

    /**
     * @throws Exception
     */
    #[Test]
    public function itHandlesAccessDeniedError(): void
    {
        $mockCallStackFactory = $this->createMock(CallStackFactory::class);
        $mockCallStack = $this->createMock(CallStack::class);
        $mockController = $this->createMock(RequestHandlerInterface::class);
        $mockRequest = $this->createMock(RequestInterface::class);
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockErrorHandler = $this->createMock(ErrorHandlerInterface::class);

        // Use a partial mock so we can check behaviour via the mocked output methods
        $japi = $this->getMockBuilder(JAPI::class)
            ->setConstructorArgs([$mockCallStackFactory, $mockErrorHandler])
            ->onlyMethods(['sendResponse'])
            ->getMock();

        $mockCallStackFactory->expects($this->once())
            ->method("make")
            ->with($mockController, $japi)
            ->willReturn($mockCallStack)
        ;

        $mockCallStack->expects($this->once())
            ->method("dispatch")
            ->with($mockRequest)
            ->willThrowException(new AccessDenied())
        ;

        $mockErrorHandler->expects($this->once())
            ->method("handle")
            ->with($this->isInstanceOf(AccessDenied::class))
            ->willReturn($mockResponse);
        ;

        // JAPI expectations
        $japi->expects($this->once())
            ->method("sendResponse")
            ->with($mockResponse)
        ;

        $japi->bootstrap($mockController, $mockRequest);
    }

    /**
     * @throws Exception
     */
    #[Test]
    public function itHandlesGeneralError(): void
    {
        $mockCallStackFactory = $this->createMock(CallStackFactory::class);
        $mockCallStack = $this->createMock(CallStack::class);
        $mockController = $this->createMock(RequestHandlerInterface::class);
        $mockRequest = $this->createMock(RequestInterface::class);
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockErrorHandler = $this->createMock(ErrorHandlerInterface::class);

        // Use a partial mock so we can check behaviour via the mocked output methods
        $japi = $this->getMockBuilder(JAPI::class)
            ->setConstructorArgs([$mockCallStackFactory, $mockErrorHandler])
            ->onlyMethods(['sendResponse'])
            ->getMock();

        $mockCallStackFactory->expects($this->once())
            ->method("make")
            ->with($mockController, $japi)
            ->willReturn($mockCallStack)
        ;

        $mockCallStack->expects($this->once())
            ->method("dispatch")
            ->with($mockRequest)
            ->willThrowException(new \RuntimeException(code: 12345))
        ;

        $mockErrorHandler->expects($this->once())
            ->method("handle")
            ->with($this->isInstanceOf(\RuntimeException::class))
            ->willReturn($mockResponse)
        ;

        // JAPI expectations
        $japi->expects($this->once())
            ->method("sendResponse")
            ->with($mockResponse)
        ;

        $japi->bootstrap($mockController, $mockRequest);
    }

    /**
     * @throws Exception
     */
    #[Test]
    public function itHandlesGeneralErrorWithValidErrorCode(): void
    {
        $mockCallStackFactory = $this->createMock(CallStackFactory::class);
        $mockCallStack = $this->createMock(CallStack::class);
        $mockController = $this->createMock(RequestHandlerInterface::class);
        $mockRequest = $this->createMock(RequestInterface::class);
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockErrorHandler = $this->createMock(ErrorHandlerInterface::class);

        // Use a partial mock so we can check behaviour via the mocked output methods
        $japi = $this->getMockBuilder(JAPI::class)
            ->setConstructorArgs([$mockCallStackFactory, $mockErrorHandler])
            ->onlyMethods(['sendResponse'])
            ->getMock();

        $mockCallStackFactory->expects($this->once())
            ->method("make")
            ->with($mockController, $japi)
            ->willReturn($mockCallStack)
        ;

        $mockCallStack->expects($this->once())
            ->method("dispatch")
            ->with($mockRequest)
            ->willThrowException(new \RuntimeException(code: ClientErrorCodes::UNAVAILABLE_FOR_LEGAL_REASONS->value))
        ;

        $mockErrorHandler->expects($this->once())
            ->method("handle")
            ->with($this->isInstanceOf(\RuntimeException::class))
            ->willReturn($mockResponse)
        ;

        // JAPI expectations
        $japi->expects($this->once())
            ->method("sendResponse")
            ->with($mockResponse)
        ;

        $japi->bootstrap($mockController, $mockRequest);
    }

    /**
     * @throws Exception
     */
    #[Test]
    public function itLogsErrorsIfGivenALogger(): void
    {
        $mockCallStackFactory = $this->createMock(CallStackFactory::class);
        $mockRequest = $this->createMock(RequestInterface::class);
        $mockLogger = $this->createMock(LoggerInterface::class);
        $mockErrorHandler = $this->createMock(ErrorHandlerInterface::class);

        // Use a partial mock so we can check behaviour via the mocked output methods
        $japi = $this->getMockBuilder(JAPI::class)
            ->setConstructorArgs([$mockCallStackFactory, $mockErrorHandler])
            ->onlyMethods(['sendResponse'])
            ->getMock()
        ;

        $mockLogger->expects($this->once())
            ->method("error")
            ->with("[JAPI] [500] Error: Unable to bootstrap")
        ;

        $japi->setLogger($mockLogger);
        $japi->bootstrap(fn() => "Hello", $mockRequest);
    }
}
