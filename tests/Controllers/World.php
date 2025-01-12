<?php

namespace Hello;

use Docnet\JAPI\Controller\Controller;

use gordonmcvey\httpsupport\enum\statuscodes\SuccessCodes;
use gordonmcvey\httpsupport\Response;

class World extends Controller
{
    public function dispatch(){
        $this->setResponse(new Response(
            SuccessCodes::OK,
            json_encode([
                'input1' => $this->getQuery('input1'),
                'input2' => $this->getPost('input2'),
                'input3' => $this->getParam('input3'),
                'input4' => $this->getParam('input4')
            ])),
        );
    }
}