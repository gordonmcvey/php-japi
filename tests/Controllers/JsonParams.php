<?php

use gordonmcvey\httpsupport\enum\statuscodes\SuccessCodes;
use gordonmcvey\httpsupport\Response;

class JsonParams extends \Docnet\JAPI\Controller
{

    public function dispatch(){
        $this->setResponse(new Response(
            SuccessCodes::OK,
            json_encode([
                'json_param'    => $this->getParam('json_param', 'default_value', true),
                'missing_param' => $this->getParam('missing_param', 'default_value', true)
            ]),
        ));
    }
}