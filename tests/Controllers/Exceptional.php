<?php

use Docnet\JAPI\Controller\Controller;
use gordonmcvey\httpsupport\RequestInterface;
use gordonmcvey\httpsupport\ResponseInterface;

class Exceptional extends Controller
{
    public function dispatch(RequestInterface $request): ?ResponseInterface
    {
        throw new RuntimeException('Error Message', 400);
    }
}
