<?php

use Docnet\JAPI\Controller\Controller;
use gordonmcvey\httpsupport\enum\statuscodes\SuccessCodes;
use gordonmcvey\httpsupport\JsonRequestInterface;
use gordonmcvey\httpsupport\RequestInterface;
use gordonmcvey\httpsupport\Response;
use gordonmcvey\httpsupport\ResponseInterface;

class JsonParams extends Controller
{

    public function dispatch(RequestInterface $request): ?ResponseInterface
    {
        /** @var JsonRequestInterface $request */
        return new Response(
            SuccessCodes::OK,
            json_encode([
                'json_param'    => $request->param('json_param', 'default_value'),
                'missing_param' => $request->param('missing_param', 'default_value')
            ]),
        );
    }
}
