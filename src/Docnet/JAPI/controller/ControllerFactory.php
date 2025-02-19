<?php

/**
 * Copyright © 2025 Gordon McVey
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

namespace Src\Docnet\JAPI\Controller;

use Docnet\JAPI\controller\RequestHandlerInterface;
use Docnet\JAPI\Exceptions\Routing;
use gordonmcvey\httpsupport\enum\statuscodes\ClientErrorCodes;

class ControllerFactory implements ControllerFactoryInterface
{
    private array $arguments = [];
    
    public function make(string $controllerClass): RequestHandlerInterface
    {
        $controller = new $controllerClass(...$this->arguments);

        if (!$controller instanceof RequestHandlerInterface) {
            throw new Routing(
                sprintf("URI path %s does not correspond to a controller", $controllerClass),
                ClientErrorCodes::BAD_REQUEST->value,
            );
        }

        return $controller;
    }

    public function withArguments(...$arguments): self
    {
        $this->arguments = $arguments;
        return $this;
    }
}
