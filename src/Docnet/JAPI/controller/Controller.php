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

use gordonmcvey\httpsupport\enum\Verbs;
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
abstract class Controller implements ControllerInterface
{
    protected ?ResponseInterface $response = null;

    /**
     * Request body decoded as json
     */
    protected mixed $requestBodyJson = null;

    public function __construct(protected readonly RequestInterface $request)
    {
    }

    /**
     * Was there an HTTP POST?
     *
     * Realistically, we're probably not going to use PUT, DELETE (for now)
     */
    final protected function isPost(): bool
    {
        return Verbs::POST === $this->request->verb();
    }

    /**
     * Get the HTTP request headers
     *
     * getallheaders() available for CGI (in addition to Apache) from PHP 5.4
     *
     * Fall back to manual processing of $_SERVER if needed
     *
     * @todo Test on Google App Engine
     *
     * @return array<string, mixed>
     */
    protected function getHeaders(): array
    {
        return $this->request->headers();
    }

    /**
     * Get the request body
     */
    protected function getBody(): ?string
    {
        return $this->request->body();
    }

    /**
     * Get the request body as a JSON object
     *
     * @return mixed
     */
    protected function getJson(): mixed
    {
        if ($this->requestBodyJson === null) {
            $this->requestBodyJson = json_decode($this->getBody());
        }
        return $this->requestBodyJson;
    }

    /**
     * Get a request parameter. Check GET then POST data, then optionally any json body data.
     */
    protected function getParam(string $key, mixed $default = null, bool $checkJsonBody = false): mixed
    {
        $param = $this->request->param($key, $default);
        if (null !== $param) {
            return $param;
        }

        // Optionally check Json in Body
        if ($checkJsonBody && isset($this->getJson()->$key)) {
            if (null !== $this->getJson()->$key) {
                return $this->getJson()->$key;
            }
        }
        return $default;
    }

    /**
     * Get a Query/GET input parameter
     */
    protected function getQuery(string $key, mixed $default = null): mixed
    {
        return $this->request->queryParam($key, $default);
    }

    /**
     * Get a POST parameter
     */
    protected function getPost(string $key, mixed $default = null): mixed
    {
        return $this->request->postParam($key, $default);
    }
}
