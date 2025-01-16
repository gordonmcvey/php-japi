<?php

use Docnet\JAPI\Controller\Controller;
use gordonmcvey\httpsupport\enum\statuscodes\SuccessCodes;
use gordonmcvey\httpsupport\RequestInterface;
use gordonmcvey\httpsupport\Response;
use gordonmcvey\httpsupport\ResponseInterface;

class Example extends Controller
{
    public function dispatch(RequestInterface $request): ?ResponseInterface
    {
        return new Response(SuccessCodes::OK, json_encode(['test' => true]));
    }
}
