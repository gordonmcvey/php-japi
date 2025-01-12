<?php

use Docnet\JAPI\Controller\Controller;
use gordonmcvey\httpsupport\ResponseInterface;

class Exceptional extends Controller
{
    public function dispatch(): ?ResponseInterface
    {
        throw new RuntimeException('Error Message', 400);
    }
}
