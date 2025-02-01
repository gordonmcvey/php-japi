<?php

namespace One\Two;

use Docnet\JAPI\controller\RequestHandlerInterface;
use gordonmcvey\httpsupport\RequestInterface;
use gordonmcvey\httpsupport\ResponseInterface;

class Three implements RequestHandlerInterface
{
    public function dispatch(RequestInterface $request): ?ResponseInterface
    {
        return null;
    }
}
