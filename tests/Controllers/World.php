<?php

namespace Hello;

use Docnet\JAPI\Controller\Controller;

use gordonmcvey\httpsupport\enum\statuscodes\SuccessCodes;
use gordonmcvey\httpsupport\RequestInterface;
use gordonmcvey\httpsupport\Response;
use gordonmcvey\httpsupport\ResponseInterface;

class World extends Controller
{
    public function dispatch(RequestInterface $request): ?ResponseInterface{
        return new Response(
            SuccessCodes::OK,
            json_encode([
                'input1' => $this->request->queryParam('input1'),
                'input2' => $this->request->postParam('input2'),
                'input3' => $this->request->param('input3'),
                'input4' => $this->request->param('input4')
            ]),
        );
    }
}
