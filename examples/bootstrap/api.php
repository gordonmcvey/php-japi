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

use Docnet\Bootstrap;
use Docnet\JAPI;
use Docnet\JAPI\controller\ControllerFactory;
use Docnet\JAPI\error\JsonErrorHandler;
use Docnet\JAPI\middleware\CallStackFactory;
use Docnet\JAPI\routing\Router;
use Docnet\JAPI\routing\SingleControllerStrategy;
use gordonmcvey\httpsupport\enum\factory\StatusCodeFactory;
use gordonmcvey\httpsupport\Request;

/**
 * Trivial JAPI bootstrap
 *
 * @author Tom Walder <tom@docnet.nu>
 */

// Includes or Auto-loader
define('BASE_PATH', dirname( __DIR__, 2));

require_once BASE_PATH . '/vendor/autoload.php';
require_once 'Hello.php';

// Demo
$request = Request::fromSuperGlobals();
(new JAPI(new CallStackFactory(), new JsonErrorHandler(new StatusCodeFactory())))
    ->bootstrap(
        new Bootstrap(
            new Router(new SingleControllerStrategy(Hello::class)),
            new ControllerFactory(),
        ),
        $request,
    )
;
