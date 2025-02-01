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

use Docnet\JAPI\controller\RequestHandlerInterface;
use Docnet\JAPI\middleware\MiddlewareProviderInterface;
use Docnet\JAPI\middleware\MiddlewareProviderTrait;
use gordonmcvey\httpsupport\enum\statuscodes\SuccessCodes;
use gordonmcvey\httpsupport\RequestInterface;
use gordonmcvey\httpsupport\Response;
use gordonmcvey\httpsupport\ResponseInterface;

/**
 * Example controller class
 *
 * @author Tom Walder <tom@docnet.nu>
 */
class Hello implements MiddlewareProviderInterface, RequestHandlerInterface
{
    use MiddlewareProviderTrait;

    /**
     * Hello, World!
     */
    public function dispatch(RequestInterface $request): ?ResponseInterface
    {
        error_log(message: sprintf("%s", __METHOD__));
        return new Response(
            SuccessCodes::OK,
            json_encode(new stdClass),
        );
    }
}
