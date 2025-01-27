<?php

use Docnet\JAPI\controller\RequestHandlerInterface;
use gordonmcvey\httpsupport\enum\statuscodes\SuccessCodes;
use gordonmcvey\httpsupport\RequestInterface;
use gordonmcvey\httpsupport\Response;
use gordonmcvey\httpsupport\ResponseInterface;

class Headers implements RequestHandlerInterface
{
    public function dispatch(RequestInterface $request): ?ResponseInterface
    {
        return new Response(SuccessCodes::OK, json_encode($request->headers()));
    }
}
