<?php

declare(strict_types=1);

namespace Docnet\JAPI\controller;

use gordonmcvey\httpsupport\ResponseInterface;

interface ControllerInterface
{
    /**
     * Main dispatch method
     */
    public function dispatch(): ?ResponseInterface;
}
