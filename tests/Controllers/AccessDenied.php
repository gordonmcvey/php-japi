<?php

use Docnet\JAPI\controller\RequestHandlerInterface;
use gordonmcvey\httpsupport\RequestInterface;
use gordonmcvey\httpsupport\ResponseInterface;

class AccessDenied implements RequestHandlerInterface
{
    public function dispatch(RequestInterface $request§): ?ResponseInterface
    {
        throw new \Docnet\JAPI\Exceptions\AccessDenied('Error Message', 403);
    }
}
