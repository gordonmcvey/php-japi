<?php

use Docnet\JAPI\Controller\Controller;
use gordonmcvey\httpsupport\enum\statuscodes\SuccessCodes;
use gordonmcvey\httpsupport\Response;
use gordonmcvey\httpsupport\ResponseInterface;

class Headers extends Controller
{
    public function dispatch(): ?ResponseInterface
    {
        return new Response(SuccessCodes::OK, json_encode($this->getHeaders()));
    }
}
