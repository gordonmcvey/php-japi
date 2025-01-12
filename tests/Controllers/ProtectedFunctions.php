<?php

use Docnet\JAPI\Controller\Controller;
use gordonmcvey\httpsupport\enum\statuscodes\SuccessCodes;
use gordonmcvey\httpsupport\Response;
use gordonmcvey\httpsupport\ResponseInterface;

class ProtectedFunctions extends Controller
{

    public function dispatch(): ?ResponseInterface
    {
        return new Response(SuccessCodes::OK, json_encode(true));
    }


    public function getIsPost(): bool
    {
        return $this->isPost();
    }
}
