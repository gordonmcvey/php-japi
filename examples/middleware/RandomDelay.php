<?php

declare(strict_types=1);

namespace gordonmcvey\JAPI\examples\middleware;

use Docnet\JAPI\interface\controller\RequestHandlerInterface;
use Docnet\JAPI\interface\middleware\MiddlewareInterface;
use gordonmcvey\httpsupport\request\RequestInterface;
use gordonmcvey\httpsupport\response\ResponseInterface;

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
