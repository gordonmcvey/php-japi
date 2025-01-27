<?php

use Docnet\JAPI\controller\RequestHandlerInterface;
use gordonmcvey\httpsupport\RequestInterface;
use gordonmcvey\httpsupport\ResponseInterface;

class Whoops implements RequestHandlerInterface
{
    public function dispatch(RequestInterface $request): ?ResponseInterface
    {
        throw new Exception;
    }
}
