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

namespace Docnet;

use Docnet\JAPI\controller\RequestHandlerInterface;
use Docnet\JAPI\error\ErrorHandlerInterface;
use Docnet\JAPI\middleware\CallStackFactory;
use Docnet\JAPI\middleware\MiddlewareProviderInterface;
use Docnet\JAPI\middleware\MiddlewareProviderTrait;
use gordonmcvey\httpsupport\enum\statuscodes\ServerErrorCodes;
use gordonmcvey\httpsupport\enum\statuscodes\SuccessCodes;
use gordonmcvey\httpsupport\RequestInterface;
use gordonmcvey\httpsupport\Response;
use gordonmcvey\httpsupport\ResponseInterface;
use Psr\Log\LoggerAwareInterface;

/**
 * Front controller for our JSON APIs
 *
 * I'm conflicted about whether or not this class adheres to PSR-1 "symbols or
 * side-effects" rule, as one or more of the methods generated output or have
 * side effects (like register_shutdown_function()).
 *
 * @author Tom Walder <tom@docnet.nu>
 */
class JAPI implements MiddlewareProviderInterface, LoggerAwareInterface
{
    use MiddlewareProviderTrait;
    use HasLogger;

    /**
     * Hook up the shutdown function so we always send nice JSON error responses
     */
    public function __construct(
        private readonly CallStackFactory $callStackFactory,
        private readonly ErrorHandlerInterface $errorHandler,
    ) {
        register_shutdown_function($this->timeToDie(...));
    }

    /**
     * Optionally, encapsulate the bootstrap in a try/catch
     */
    public function bootstrap(RequestHandlerInterface|callable $controllerSource, RequestInterface $request): void
    {
        try {
            $controller = is_callable($controllerSource) ? $controllerSource($request) : $controllerSource;
            if (!$controller instanceof RequestHandlerInterface) {
                throw new \Exception('Unable to bootstrap', ServerErrorCodes::INTERNAL_SERVER_ERROR->value);
            }
            $response = $this->dispatch($controller, $request);
        } catch (\Throwable $e) {
            $this->getLogger()->error("[JAPI] [{$e->getCode()}] Error: {$e->getMessage()}");
            $response = $this->errorHandler->handle($e);
        } finally {
            isset($response) && $this->sendResponse($response);
        }
    }

    /**
     * Go, Johnny, Go!
     *
     * If the controller to be dispatched implements MiddlewareProviderInterface, then its middleware will be added to
     * the call stack on creation, then the JAPI middleware will be added.  Otherwise, only the JAPI middleware is
     * added to the call stack.
     */
    private function dispatch(RequestHandlerInterface $controller, RequestInterface $request): ResponseInterface
    {
        $callStack = $this->callStackFactory->make($controller, $this);
        $response = $callStack->dispatch($request) ?? new Response(SuccessCodes::NO_CONTENT, '');

        return $response;
    }

    /**
     * Custom shutdown function
     */
    public function timeToDie(): void
    {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_USER_ERROR, E_COMPILE_ERROR])) {
            $errorCode = ServerErrorCodes::INTERNAL_SERVER_ERROR;
            $this->sendResponse($this->errorHandler->handle(new \ErrorException(
                $error['message'],
                $errorCode->value,
                0,
                $error['file'],
                $error['line'],
            )));
        }
    }

    /**
     * Output the response as JSON with HTTP headers
     */
    protected function sendResponse(ResponseInterface $response): void
    {
        $response->sendHeaders();
        echo $response->body();
    }
}
