<?php

use Docnet\JAPI\Controller\Controller;
use gordonmcvey\httpsupport\RequestInterface;
use gordonmcvey\httpsupport\ResponseInterface;

class AccessDenied extends Controller
{
    public function dispatch(RequestInterface $request§): ?ResponseInterface
    {
        throw new \Docnet\JAPI\Exceptions\AccessDenied('Error Message', 403);
    }
}
