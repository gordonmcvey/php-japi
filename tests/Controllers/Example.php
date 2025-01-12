<?php

use Docnet\JAPI\Controller\Controller;
use gordonmcvey\httpsupport\enum\statuscodes\SuccessCodes;
use gordonmcvey\httpsupport\Response;
use gordonmcvey\httpsupport\ResponseInterface;

class Example extends Controller
{
    public function dispatch(): ?ResponseInterface
    {
        return new Response(SuccessCodes::OK, json_encode(['test' => true]));
    }
}
