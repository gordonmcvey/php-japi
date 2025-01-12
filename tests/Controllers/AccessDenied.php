<?php

use Docnet\JAPI\Controller\Controller;
use gordonmcvey\httpsupport\ResponseInterface;

class AccessDenied extends Controller
{
    public function dispatch(): ?ResponseInterface
    {
        throw new \Docnet\JAPI\Exceptions\AccessDenied('Error Message', 403);
    }
}
