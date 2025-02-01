<?php

declare(strict_types=1);

use Docnet\JAPI\controller\RequestHandlerInterface;
use Docnet\JAPI\middleware\MiddlewareInterface;
use gordonmcvey\httpsupport\RequestInterface;
use gordonmcvey\httpsupport\ResponseInterface;

/**
 * Middleware to add a randomised delay into the request/response cycle of 0 .. 1 second
 *
 * This exists basically to demonstrate the Profiler middleware
 */
class RandomDelay implements MiddlewareInterface
{
    public function handle(RequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $delay = mt_rand(0, 1000000);

        error_log(message: sprintf("%s: %d", __METHOD__, $delay));
        usleep($delay);
        return $handler->dispatch($request);
    }
}
