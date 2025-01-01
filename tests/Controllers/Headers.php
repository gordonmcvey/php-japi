<?php

use gordonmcvey\httpsupport\enum\statuscodes\SuccessCodes;
use gordonmcvey\httpsupport\Response;

class Headers extends \Docnet\JAPI\Controller
{
    public function dispatch(){
        $this->setResponse(new Response(SuccessCodes::OK, json_encode($this->getHeaders())));
    }
}