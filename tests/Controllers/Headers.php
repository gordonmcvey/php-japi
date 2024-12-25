<?php

use Docnet\JAPI\Http\Enum\SuccessCodes;
use Docnet\JAPI\Http\Response;

class Headers extends \Docnet\JAPI\Controller
{
    public function dispatch(){
        $this->setResponse(new Response(SuccessCodes::OK, json_encode($this->getHeaders())));
    }
}