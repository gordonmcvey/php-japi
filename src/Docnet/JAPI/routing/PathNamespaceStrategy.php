<?php

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

declare(strict_types=1);

namespace Docnet\JAPI\routing;

class PathNamespaceStrategy implements RoutingStrategyInterface
{
    /**
     * @param string $controllerNamespace The namespace controllers will be located under, eg vendor\project\controllers
     */
    public function __construct(private readonly string $controllerNamespace = '')
    {
    }

    public function route(string $path): ?string
    {
        return sprintf(
            "%s%s",
            $this->controllerNamespace,
            str_replace(
                [" ", "\t"],
                ["\\", ""],
                ucwords(str_replace(
                    ["/", "-", "_"],
                    [" ", "\t", "\t"],
                    strtolower($path)
                ))
            ),
        );
    }
}
