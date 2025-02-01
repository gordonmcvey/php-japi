<?php

declare(strict_types=1);

use Docnet\JAPI\controller\RequestHandlerInterface;
use Docnet\JAPI\middleware\MiddlewareInterface;
use gordonmcvey\httpsupport\RequestInterface;
use gordonmcvey\httpsupport\ResponseInterface;

/**
 * Request/response cycle profiler
 * 
 * This class tags every request/response cycle with a "unique" ID and logs how long it took for everything in the call
 * stack to execute.  As such it should be the outer-most middleware in your stack.
 */
class Profiler implements MiddlewareInterface
{
    public function handle(RequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {  
        $start = microtime(true);
        $id = uniqid('', true);

        // Demonstrate how we might go about adding a unique identifier to a specific request for logging purposes
        $request->setHeader("X-Middleware-Profile-Request-Id", $id);
        error_log("Request ID $id started");

        try {
            return $handler->dispatch($request);
        } finally {
            error_log(sprintf(
                "Request ID %s ended, took %f milisecond(s)",
                $id,
                (microtime(true) - $start) * 1000,
            ));
        }
    }
}
