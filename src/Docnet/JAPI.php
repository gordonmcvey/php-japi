<?php

declare(strict_types= 1);

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
namespace Docnet;

use Docnet\JAPI\Controller;
use Docnet\JAPI\Exceptions\Routing as RoutingException;
use Docnet\JAPI\Exceptions\Auth as AuthException;
use Docnet\JAPI\Exceptions\AccessDenied as AccessDeniedException;
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
     */
    public function __construct(private readonly bool $exposeErrors = false)
    {
        register_shutdown_function($this->timeToDie(...));
    }

    /**
     * Optionally, encapsulate the bootstrap in a try/catch
     */
    public function bootstrap(Controller|callable $controllerSource): void
    {
        try {
            $controller = is_callable($controllerSource) ? $controllerSource() : $controllerSource;
            if($controller instanceof Controller) {
                $this->dispatch($controller);
            } else {
                throw new \Exception('Unable to bootstrap', 500);
            }
        } catch (RoutingException $e) {
            $this->jsonError($e, 404);
        } catch (AuthException $e) {
            $this->jsonError($e, 401);
        } catch (AccessDeniedException $e) {
            $this->jsonError($e, 403);
        } catch (\Exception $e) {
            $this->jsonError($e, $e->getCode());
        }
    }

    /**
     * Go, Johnny, Go!
     *
     * @param Controller $controller
     */
    public function dispatch(Controller $controller): void
    {
        $controller->preDispatch();
        $controller->dispatch();
        $controller->postDispatch();
        $this->sendResponse($controller->getResponse());
    }

    /**
     * Custom shutdown function
     */
    public function timeToDie(): void
    {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_USER_ERROR, E_COMPILE_ERROR])) {
            $this->jsonError(new \ErrorException($error['message'], 500, 0, $error['file'], $error['line']), 500);
        }
    }

    /**
     * Whatever went wrong, let 'em have it in JSON over HTTP
     *
     * @param \Exception $error
     * @param int $code
     */
    protected function jsonError(\Exception $error, int $code): void
    {
        $response = [
            'code' => $code,
            'msg' => ($error instanceof \ErrorException ? 'Internal Error' : 'Exception')
        ];
        $logMessage = get_class($error) . ': ' . $error->getMessage();
        if($this->exposeErrors) {
            $response['detail'] = $logMessage;
        }
        if($code < 400 || $code > 505) {
            $code = 500;
        }
        $this->sendResponse($response, $code);
        $this->getLogger()->error("[JAPI] [{$code}] Error: {$logMessage}");
    }

    /**
     * Output the response as JSON with HTTP headers
     *
     * @param array|object $response
     * @param int $httpCode
     */
    protected function sendResponse(array|object|null $response, int $httpCode = 200)
    {
        $httpCode = min(max($httpCode, 100), 505);
        http_response_code($httpCode);
        header('Content-type: application/json');
        echo json_encode($response);
    }

    /**
     * Tell JAPI to expose error detail, or not!
     *
     * @param bool $exposeErrors
     */
    public function exposeErrorDetail(bool $exposeErrors = true): void
    {
        $this->exposeErrors = $exposeErrors;
    }
}
