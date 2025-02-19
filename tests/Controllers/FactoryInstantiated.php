<?php

namespace Docnet\JAPI\test\Controllers;

use Docnet\JAPI\controller\RequestHandlerInterface;
use gordonmcvey\httpsupport\RequestInterface;
use gordonmcvey\httpsupport\ResponseInterface;

readonly class FactoryInstantiated implements RequestHandlerInterface
{
    public function __construct(public ?string $arg1 = null, public ?int $arg2 = null, public ?bool $arg3 = null)
    {
    }

    public function dispatch(RequestInterface $request): ?ResponseInterface
    {
        return null;
    }
}
