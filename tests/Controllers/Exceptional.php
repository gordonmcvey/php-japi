<?php

use Docnet\JAPI\controller\RequestHandlerInterface;
use gordonmcvey\httpsupport\RequestInterface;
use gordonmcvey\httpsupport\ResponseInterface;

class Exceptional implements RequestHandlerInterface
{
    public function dispatch(RequestInterface $request): ?ResponseInterface
    {
        throw new RuntimeException('Error Message', 400);
    }
}
