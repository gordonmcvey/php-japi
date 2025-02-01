<?php

declare(strict_types=1);

namespace Docnet\JAPI\controller;

use gordonmcvey\httpsupport\RequestInterface;
use gordonmcvey\httpsupport\ResponseInterface;

interface RequestHandlerInterface
{
    /**
     * Main dispatch method
     */
    public function dispatch(RequestInterface $request): ?ResponseInterface;
}
