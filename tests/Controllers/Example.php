<?php

use gordonmcvey\httpsupport\enum\statuscodes\SuccessCodes;
use gordonmcvey\httpsupport\Response;

class Example extends \Docnet\JAPI\Controller
{
    public function dispatch(){
        $this->setResponse(new Response(SuccessCodes::OK, json_encode(['test' => true])));
    }
}