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
namespace Docnet\JAPI;

use Docnet\JAPI\Exceptions\Routing;

/**
 * Router for our revised "Single Action Controller" approach
 *
 * @package Docnet\App
 */
class SolidRouter
{

    /**
     * URL to route
     */
    protected string $url = '';

    /**
     * Output from parse_url()
     *
     * @var array|mixed
     */
    protected array $parsedUrl = [];

    /**
     * Controller class as determined by parseController()
     */
    protected string $controllerClass = '';

    /**
     * Static routes
     *
     */
    private array $staticRoutes = [];

    /**
     * We need to know the base namespace for the controller
     *
     * @param string $controllerNamespace Namespace for the controller
     */
    public function __construct(private readonly string $controllerNamespace = '\\')
    {
    }

    /**
     * Route the request.
     *
     * This means "turn the URL into a Controller (class) for execution.
     *
     * Keep URL string and parse_url array response as member vars in case we
     * want to evaluate later.
     *
     * @throws Routing
     */
    public function route(?string $url = null): static
    {
        $this->url = (null === $url ? $_SERVER['REQUEST_URI'] : $url);
        $locallyParsedUrl = parse_url($this->url);

        if (!$locallyParsedUrl || !isset($locallyParsedUrl['path'])) {
            throw new Routing('URL parse error (parse_url): ' . $this->url);
        }
        $this->parsedUrl = $locallyParsedUrl;

        if (!$this->routeStatic()) {
            if (!(bool)preg_match_all("#/(?<controller>[\w\-]+)#", $this->parsedUrl['path'], $matches)) {
                throw new Routing('URL parse error (preg_match): ' . $this->url);
            }
            $this->setup(implode("\t", $matches['controller']));
        }

        return $this;
    }

    /**
     * Check for static routes, setup if needed
     *
     * @return bool
     */
    protected function routeStatic(): bool
    {
        if (isset($this->staticRoutes[$this->parsedUrl['path']])) {
            $this->setup($this->staticRoutes[$this->parsedUrl['path']], NULL, FALSE);
            return TRUE;
        }
        return FALSE;
    }

    /**
     * Check & store controller from URL parts
     *
     * @param $str_controller
     * @param $bol_parse
     * @throws Routing
     */
    protected function setup($str_controller, $bol_parse = TRUE)
    {
        $this->controllerClass = ($bol_parse ? $this->parseController($str_controller) : $str_controller);
        if (!method_exists($this->controllerClass, 'dispatch')) {
            throw new Routing("Could not find controller: {$this->controllerClass}");
        }
    }

    /**
     * Translate URL controller name into name-spaced class
     */
    protected function parseController(string $controllerName): string
    {
        return $this->controllerNamespace . str_replace([" ", "\t"], ["", '\\'], ucwords(str_replace("-", " ", strtolower($controllerName))));
    }

    /**
     * Get the routed controller
     */
    public function getController(): string
    {
        return $this->controllerClass;
    }

    /**
     * Add a single static route
     */
    public function addRoute(string $path, string $controllerName): static
    {
        $this->staticRoutes[$path] = $controllerName;
        return $this;
    }

    /**
     * Bulk-set the static routes
     */
    public function setRoutes(array $staticRoutes): static
    {
        $this->staticRoutes = $staticRoutes;
        return $this;
    }
}
