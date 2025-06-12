<?php

declare(strict_types=1);

namespace gordonmcvey\JAPI\test\unit;

use Docnet\JAPI\interface\error\ErrorHandlerInterface;
use Docnet\JAPI\ShutdownHandler;
use gordonmcvey\httpsupport\enum\statuscodes\ServerErrorCodes;
use gordonmcvey\httpsupport\response\ResponseInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ShutdownHandlerTest extends TestCase
{
    /**
     * @throws Exception
     */
    #[Test]
    public function itHandlesNormalShutdown(): void
    {
        /** @var ErrorHandlerInterface&MockObject $errorHandler */
        $errorHandler = $this->createMock(ErrorHandlerInterface::class);

        /** @var ShutdownHandler&MockObject $handler */
        $handler = $this
            ->getMockBuilder(ShutdownHandler::class)
            ->setConstructorArgs([$errorHandler])
            ->onlyMethods(["getLastError", "flushBuffers"])
            ->getMock()
        ;

        $handler->expects($this->once())->method("getLastError")->willReturn(null);
        $errorHandler->expects($this->never())->method("handle");

        $handler();
    }

    /**
     * @throws Exception
     */
    #[Test]
    public function itHandlesSupportedErrors(): void
    {
        /** @var ErrorHandlerInterface&MockObject $errorHandler */
        $errorHandler = $this->createMock(ErrorHandlerInterface::class);

        /** @var ResponseInterface&MockObject $response */
        $response = $this->createMock(ResponseInterface::class);

        /** @var ShutdownHandler&MockObject $handler */
        $handler = $this
            ->getMockBuilder(ShutdownHandler::class)
            ->setConstructorArgs([$errorHandler])
            ->onlyMethods(["getLastError", "flushBuffers"])
            ->getMock()
        ;

        $handler
            ->expects($this->once())
            ->method("getLastError")
            ->willReturn([
                "message" => "I'm a handled error",
                "type"    => E_USER_ERROR,
                "file"    => __FILE__,
                "line"    => 123,
            ])
        ;

        $errorHandler
            ->expects($this->once())
            ->method("handle")
            ->with($this->callback(
                fn($e): bool
                    => $e instanceof \ErrorException
                    && "I'm a handled error" === $e->getMessage()
                    && ServerErrorCodes::INTERNAL_SERVER_ERROR->value === $e->getCode()
                    && __FILE__ === $e->getFile()
                    && 123 === $e->getLine()
                    && E_USER_ERROR === $e->getSeverity()
            ))
            ->willReturn($response)
        ;

        $response->expects($this->once())->method("sendHeaders")->willReturnSelf();
        $response->expects($this->once())->method("body")->willReturn("");

        $handler();
    }

    /**
     * @throws Exception
     */
    #[Test]
    public function itSkipsUnsupportedErrors(): void
    {
        /** @var ErrorHandlerInterface&MockObject $errorHandler */
        $errorHandler = $this->createMock(ErrorHandlerInterface::class);

        /** @var ShutdownHandler&MockObject $handler */
        $handler = $this
            ->getMockBuilder(ShutdownHandler::class)
            ->setConstructorArgs([$errorHandler])
            ->onlyMethods(["getLastError", "flushBuffers"])
            ->getMock()
        ;

        $handler
            ->expects($this->once())
            ->method("getLastError")
            ->willReturn([
                "message" => "I'm an unhandled error",
                "type"    => E_USER_DEPRECATED,
                "file"    => __FILE__,
                "line"    => 123,
            ])
        ;

        $errorHandler
            ->expects($this->never())
            ->method("handle")
        ;

        $handler();
    }
}
