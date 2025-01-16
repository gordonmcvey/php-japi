<?php

use Docnet\JAPI\Controller\Controller;
use gordonmcvey\httpsupport\RequestInterface;
use gordonmcvey\httpsupport\ResponseInterface;

class Whoops extends Controller
{
    public function dispatch(RequestInterface $request): ?ResponseInterface
    {
        throw new Exception;
    }
}
