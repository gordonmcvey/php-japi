<?php

/**
 * Copyright 2015 Docnet
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

namespace Docnet\JAPI\controller;

use gordonmcvey\httpsupport\RequestInterface;
use gordonmcvey\httpsupport\ResponseInterface;

/**
 * Base Controller
 *
 * There's some stuff in here which feels like it should be part of a "Request"
 * object but, we'll leave it here for now!
 *
 * @author Tom Walder <tom@docnet.nu>
 * @abstract
 */
abstract class Controller implements RequestHandlerInterface
{
    protected ?ResponseInterface $response = null;

    /**
     * Request body decoded as json
     */
    protected mixed $requestBodyJson = null;

    /**
     * @todo Remove request object from constructor
     */
    public function __construct(protected readonly RequestInterface $request)
    {
    }
}
