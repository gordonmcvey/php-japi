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

use Docnet\JAPI\controller\Controller;
use Docnet\JAPI\Exceptions\Routing as RoutingException;
use Docnet\JAPI\Exceptions\Auth as AuthException;
use Docnet\JAPI\Exceptions\AccessDenied as AccessDeniedException;
use gordonmcvey\httpsupport\enum\factory\StatusCodeFactory;
use gordonmcvey\httpsupport\enum\statuscodes\ClientErrorCodes;
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
class JAPI implements LoggerAwareInterface
{
    use HasLogger;

    /**
     * Hook up the shutdown function so we always send nice JSON error responses
     *
     * @param bool $exposeErrors Set to true if you want to include more detailed debugging data in error output
     * @param int $jsonFlags Flag mask for the encoded JSON output.  See the PHP manual for json_encode for valid flags
     */
    public function __construct(
        private readonly StatusCodeFactory $codeFactory,
        private bool $exposeErrors = false,
        private readonly int $jsonFlags = 0
    ) {
        register_shutdown_function($this->timeToDie(...));
    }

    /**
     * Optionally, encapsulate the bootstrap in a try/catch
     */
    public function bootstrap(Controller|callable $controllerSource, RequestInterface $request): void
    {
        try {
            $controller = is_callable($controllerSource) ? $controllerSource() : $controllerSource;
            if ($controller instanceof Controller) {
                $this->dispatch($controller, $request);
            } else {
                throw new \Exception('Unable to bootstrap', ServerErrorCodes::INTERNAL_SERVER_ERROR->value);
            }
        } catch (RoutingException $e) {
            $this->jsonError($e, ClientErrorCodes::NOT_FOUND);
        } catch (AuthException $e) {
            $this->jsonError($e, ClientErrorCodes::UNAUTHORIZED);
        } catch (AccessDeniedException $e) {
            $this->jsonError($e, ClientErrorCodes::FORBIDDEN);
        } catch (\Exception $e) {
            $code = $this->codeFactory->fromThrowable($e);
            $this->jsonError($e, $code);
        }
    }

    /**
     * Go, Johnny, Go!
     *
     * @param Controller $controller
     */
    public function dispatch(Controller $controller, RequestInterface $request): void
    {
        $response = $controller->dispatch($request) ?? new Response(SuccessCodes::NO_CONTENT, '');
        $this->sendResponse($response);
    }

    /**
     * Custom shutdown function
     */
    public function timeToDie(): void
    {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_USER_ERROR, E_COMPILE_ERROR])) {
            $errorCode = ServerErrorCodes::INTERNAL_SERVER_ERROR;
            $this->jsonError(new \ErrorException(
                $error['message'],
                $errorCode->value,
                0,
                $error['file'],
                $error['line'],
            ), $errorCode);
        }
    }

    /**
     * Whatever went wrong, let 'em have it in JSON over HTTP
     */
    protected function jsonError(\Exception $error, ClientErrorCodes|ServerErrorCodes $code): void
    {
        $logMessage = sprintf("%s: %s", get_class($error), $error->getMessage());
        $payload = [
            'code' => $code->value,
            'msg' => ($error instanceof \ErrorException ? 'Internal Error' : 'Exception')
        ];
        if ($this->exposeErrors) {
            $payload['detail'] = $logMessage;
        }

        $this->sendResponse(new Response($code, (string) json_encode($payload, $this->jsonFlags)));
        $this->getLogger()->error("[JAPI] [{$code->value}] Error: {$logMessage}");
    }

    /**
     * Output the response as JSON with HTTP headers
     */
    protected function sendResponse(ResponseInterface $response): void
    {
        $response->sendHeaders();
        echo $response->body();
    }

    /**
     * Tell JAPI to expose error detail, or not!
     */
    public function exposeErrorDetail(bool $exposeErrors = true): void
    {
        $this->exposeErrors = $exposeErrors;
    }
}
