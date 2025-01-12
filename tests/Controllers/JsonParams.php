<?php

use Docnet\JAPI\Controller\Controller;
use gordonmcvey\httpsupport\enum\statuscodes\SuccessCodes;
use gordonmcvey\httpsupport\Response;
use gordonmcvey\httpsupport\ResponseInterface;

class JsonParams extends Controller
{

    public function dispatch(): ?ResponseInterface
    {
        return new Response(
            SuccessCodes::OK,
            json_encode([
                'json_param'    => $this->getParam('json_param', 'default_value', true),
                'missing_param' => $this->getParam('missing_param', 'default_value', true)
            ]),
        );
    }
}
