<?php

/**
 * Copyright 2025 Gordon McVey
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

declare(strict_types=1);

namespace Docnet;

use Docnet\JAPI\controller\ControllerFactory;
use Docnet\JAPI\controller\RequestHandlerInterface;
use Docnet\JAPI\routing\RouterInterface;
use gordonmcvey\httpsupport\RequestInterface;

/**
 * Front controller for our JSON APIs
 *
 * I'm conflicted about whether or not this class adheres to PSR-1 "symbols or
 * side-effects" rule, as one or more of the methods generated output or have
 * side effects (like register_shutdown_function()).
 *
 * @author Tom Walder <tom@docnet.nu>
 */
readonly class Bootstrap
{
    public function __construct(
        private RouterInterface $router,
        private ControllerFactory $controllerFactory,
    ) {
    }

    public function __invoke(RequestInterface $request): RequestHandlerInterface
    {
        return $this->controllerFactory->make($this->router->route($request));
    }
}
