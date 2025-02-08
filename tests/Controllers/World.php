<?php

namespace Hello;

use Docnet\JAPI\controller\RequestHandlerInterface;
use gordonmcvey\httpsupport\enum\statuscodes\SuccessCodes;
use gordonmcvey\httpsupport\RequestInterface;
use gordonmcvey\httpsupport\Response;
use gordonmcvey\httpsupport\ResponseInterface;

class World implements RequestHandlerInterface
{
    public function dispatch(RequestInterface $request): ?ResponseInterface{
        return new Response(
            SuccessCodes::OK,
            json_encode([
                'input1' => $request->queryParam('input1'),
                'input2' => $request->postParam('input2'),
                'input3' => $request->param('input3'),
                'input4' => $request->param('input4')
            ]),
        );
    }
}
