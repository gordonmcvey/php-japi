<?php

namespace YoDawg;

use Docnet\JAPI\controller\RequestHandlerInterface;
use gordonmcvey\httpsupport\RequestInterface;
use gordonmcvey\httpsupport\ResponseInterface;

class HeardYoLike implements RequestHandlerInterface
{
    public function dispatch(RequestInterface $requestInterface): ?ResponseInterface
    {
        return null;
    }
}
