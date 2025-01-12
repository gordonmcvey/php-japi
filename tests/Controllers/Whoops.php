<?php

use Docnet\JAPI\Controller\Controller;
use gordonmcvey\httpsupport\ResponseInterface;

class Whoops extends Controller
{
    public function dispatch(): ?ResponseInterface
    {
        throw new Exception;
    }
}
