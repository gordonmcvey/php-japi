<?php

declare(strict_types=1);

namespace Docnet\JAPI\test\integration\middleware;

use Docnet\JAPI\controller\RequestHandlerInterface;
use Docnet\JAPI\middleware\CallStack;
use Docnet\JAPI\middleware\MiddlewareInterface;
use gordonmcvey\httpsupport\enum\statuscodes\SuccessCodes;
use gordonmcvey\httpsupport\Request;
use gordonmcvey\httpsupport\RequestInterface;
use gordonmcvey\httpsupport\Response;
use gordonmcvey\httpsupport\ResponseInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class CallStackTest extends TestCase
{
    #[Test]
    public function itSupportsMiddlewareChaining(): void
    {
        $controller = new class implements RequestHandlerInterface
        {
            public function dispatch(RequestInterface $request): ResponseInterface {
                return new Response(SuccessCodes::OK, "<p>I'm the controller</p>\n");
            }
        };

        $outer = new class implements MiddlewareInterface
        {
            public function handle(RequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
                $response = $handler->dispatch($request);
                return new Response(SuccessCodes::OK, $response->body() . "<p>I'm the outer middleware</p>\n");
            }
        };

        $inner = new class implements MiddlewareInterface
        {
            public function handle(RequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
                $response = $handler->dispatch($request);
                return new Response(SuccessCodes::OK, $response->body() . "<p>I'm the inner middleware</p>\n");
            }
        };

        $request = new Request([],[], [], [], []);

        $callstack = new CallStack($controller);
        $callstack->add($inner)->add($outer);
        $response = $callstack->dispatch($request);

        // The stack should be called in the order outer -> inner -> controller 
        // and should return in the order controller -> inner -> outer
        $this->assertSame("<p>I'm the controller</p>\n" 
            . "<p>I'm the inner middleware</p>\n" 
            . "<p>I'm the outer middleware</p>\n", $response->body());
    }

    #[Test]
    public function itSupportsMiddlewareShortCircuiting(): void
    {
        $controller = new class implements RequestHandlerInterface
        {
            public function dispatch(RequestInterface $request): ResponseInterface {
                return new Response(SuccessCodes::OK, "<p>I'm the controller</p>\n");
            }
        };

        $outer = new class implements MiddlewareInterface
        {
            public function handle(RequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
                return new Response(SuccessCodes::OK, "<p>I'm the outer middleware</p>\n");
            }
        };

        $inner = new class implements MiddlewareInterface
        {
            public function handle(RequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
                $response = $handler->dispatch($request);
                return new Response(SuccessCodes::OK, $response->body() . "<p>I'm the inner middleware</p>\n");
            }
        };

        $request = new Request([],[], [], [], []);

        $callstack = new CallStack($controller);
        $callstack->add($inner)->add($outer);
        $response = $callstack->dispatch($request);

        // Only the outermost middleware should be executed
        $this->assertSame("<p>I'm the outer middleware</p>\n", $response->body());
    }

    #[Test]
    public function itSupportsMiddlewareConditionalShortCircuiting(): void
    {
        $controller = new class implements RequestHandlerInterface
        {
            public function dispatch(RequestInterface $request): ResponseInterface {
                return new Response(SuccessCodes::OK, "<p>I'm the controller</p>\n");
            }
        };

        $outer = new class implements MiddlewareInterface
        {
            public function handle(RequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
                $response = $handler->dispatch($request);

                if ("trigger" === $request->header("X-Outer-Condition")) {
                    $response = new Response(SuccessCodes::OK, $response->body() . "<p>The outer middleware was triggered</p>\n");
                }

                return $response;
            }
        };

        $inner = new class implements MiddlewareInterface
        {
            public function handle(RequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
                $response = $handler->dispatch($request);

                if ("trigger" === $request->header("X-Inner-Condition")) {
                    $response = new Response(SuccessCodes::OK, $response->body() . "<p>The inner middleware was triggered</p>\n");
                }

                return $response;
            }
        };

        $callstack = new CallStack($controller);
        $callstack->add($inner)->add($outer);

        $request = new Request([],[], [], [], []);
        $response = $callstack->dispatch($request);
        $this->assertSame("<p>I'm the controller</p>\n", $response->body());

        $request = new Request([],[], [], [], [
            "HTTP_X_OUTER_CONDITION" => "trigger",
        ]);
        $response = $callstack->dispatch($request);
        $this->assertSame("<p>I'm the controller</p>\n" 
            . "<p>The outer middleware was triggered</p>\n", $response->body());

        $request = new Request([],[], [], [], [
            "HTTP_X_INNER_CONDITION" => "trigger",
        ]);

        $response = $callstack->dispatch($request);

        $this->assertSame("<p>I'm the controller</p>\n" 
            . "<p>The inner middleware was triggered</p>\n", $response->body());

            $request = new Request([],[], [], [], [
            "HTTP_X_OUTER_CONDITION" => "trigger",
            "HTTP_X_INNER_CONDITION" => "trigger",
        ]);
        $response = $callstack->dispatch($request);
        $this->assertSame("<p>I'm the controller</p>\n" 
            . "<p>The inner middleware was triggered</p>\n" 
            . "<p>The outer middleware was triggered</p>\n", $response->body());
    }
}
