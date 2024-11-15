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

namespace Docnet\JAPI;

/**
 * Base Controller
 *
 * There's some stuff in here which feels like it should be part of a "Request"
 * object but, we'll leave it here for now!
 *
 * @author Tom Walder <tom@docnet.nu>
 * @abstract
 */
abstract class Controller
{
    /**
     * Response data
     *
     * @var array<array-key, mixed>|object|null
     */
    protected object|array|null $response = null;

    /**
     * Request body
     */
    protected ?string $requestBody = null;

    /**
     * Request body decoded as json
     */
    protected mixed $requestBodyJson = null;

    /**
     * Default, empty pre dispatch
     *
     * Usually overridden for authentication
     */
    public function preDispatch(): void
    {
    }

    /**
     * Default, empty post dispatch
     *
     * Available for override - perhaps for UOW DB writes?
     */
    public function postDispatch(): void
    {
    }

    /**
     * Was there an HTTP POST?
     *
     * Realistically, we're probably not going to use PUT, DELETE (for now)
     */
    final protected function isPost(): bool
    {
        return ($_SERVER['REQUEST_METHOD'] === 'POST');
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
        if (function_exists('getallheaders')) {
            return getallheaders();
        }
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))))] = $value;
            }
        }
        return $headers;
    }

    /**
     * Get the request body
     */
    protected function getBody(): ?string
    {
        if ($this->requestBody === null) {
            // We store this as prior to php5.6 this can only be read once
            $this->requestBody = (string) file_get_contents('php://input');
        }
        return $this->requestBody;
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
        $query = $this->getQuery($key);
        if (null !== $query) {
            return $query;
        }
        $post = $this->getPost($key);
        if (null !== $post) {
            return $post;
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
        return (isset($_GET[$key]) ? $_GET[$key] : $default);
    }

    /**
     * Get a POST parameter
     */
    protected function getPost(string $key, mixed $default = null): mixed
    {
        return (isset($_POST[$key]) ? $_POST[$key] : $default);
    }

    /**
     * Set the response object
     *
     * @param array<array-key, mixed>|object|null $response
     */
    protected function setResponse(object|array|null $response): void
    {
        $this->response = $response;
    }

    /**
     * Get the response data
     *
     * @return array<array-key, mixed>|object|null
     */
    public function getResponse(): array|object|null
    {
        return $this->response;
    }

    /**
     * Main dispatch method
     *
     * @return mixed
     */
    abstract public function dispatch();
}
