<?php

declare(strict_types=1);

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

use Docnet\JAPI\controller\RequestHandlerInterface;
use Docnet\JAPI\error\JsonErrorHandler;
use Docnet\JAPI\JAPI;
use Docnet\JAPI\middleware\CallStackFactory;
use Docnet\JAPI\routing\Router;
use Docnet\JAPI\routing\SingleControllerStrategy;
use gordonmcvey\httpsupport\enum\factory\StatusCodeFactory;
use gordonmcvey\httpsupport\Request;
use gordonmcvey\httpsupport\RequestInterface;

/**
 * Trivial JAPI bootstrap
 *
 * @author Tom Walder <tom@docnet.nu>
 */

// Includes or Auto-loader
define('BASE_PATH', dirname( __DIR__, 2));

require_once BASE_PATH . '/vendor/autoload.php';
require_once 'AddParameter.php';
require_once 'Hello.php';
require_once "Profiler.php";
require_once "RandomDelay.php";

// Demo
$request = Request::fromSuperGlobals();
(new JAPI(new CallStackFactory(), new JsonErrorHandler(new StatusCodeFactory())))
    ->addMiddleware(new AddParameter("globalMessage1", "Hello"))
    ->addMiddleware(new AddParameter("globalMessage2", "World"))
    ->addMiddleware(new AddParameter("globalMessage3", "Hello, World!"))
    ->addMiddleware(new RandomDelay)
    ->addMiddleware(new Profiler)
    ->bootstrap(
            function(RequestInterface $request): RequestHandlerInterface {
            $router = new Router(new SingleControllerStrategy(Hello::class));
            $controllerClass = $router->route($request);

            return (new $controllerClass)
                ->addMiddleware(new AddParameter("controllerMessage1", "Hello"))
                ->addMiddleware(new AddParameter("controllerMessage2", "World"))
                ->addMiddleware(new AddParameter("controllerMessage3", "Hello, World!"))
                ->addMiddleware(new AddParameter("addedBy", __FUNCTION__))
                ->addMiddleware(new RandomDelay);
        },
        $request
    )
;
