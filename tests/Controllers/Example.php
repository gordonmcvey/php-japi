<?php

use Docnet\JAPI\Controller\Controller;
use gordonmcvey\httpsupport\enum\statuscodes\SuccessCodes;
use gordonmcvey\httpsupport\Response;

class Example extends Controller
{
    public function dispatch(){
        $this->setResponse(new Response(SuccessCodes::OK, json_encode(['test' => true])));
    }
}