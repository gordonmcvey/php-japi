<?php

namespace One\Two;

use Docnet\JAPI\Controller\Controller;
use gordonmcvey\httpsupport\RequestInterface;
use gordonmcvey\httpsupport\ResponseInterface;

class Three extends Controller
{
    public function dispatch(RequestInterface $request): ?ResponseInterface
    {
        return null;
    }
}
