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

namespace Docnet\JAPI\test\integration\error;

use Docnet\JAPI\error\JsonErrorHandler;
use gordonmcvey\httpsupport\enum\factory\StatusCodeFactory;
use gordonmcvey\httpsupport\enum\statuscodes\ClientErrorCodes;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class JsonErrorHandlerTest extends TestCase
{
    #[Test]
    public function itHandlesAnError(): void
    {
        $handler = new JsonErrorHandler(new StatusCodeFactory());
        
        $response = $handler->handle(new \Exception("Test", ClientErrorCodes::NOT_FOUND->value));
        $payload = $response->body();
        $decodedPayload = json_decode($payload);

        $this->assertSame("text/json", $response->contentType());
        $this->assertSame(ClientErrorCodes::NOT_FOUND, $response->responseCode());

        $this->assertJson($payload);
        $this->assertObjectHasProperty("msg", $decodedPayload);
        $this->assertObjectHasProperty("code", $decodedPayload);
        $this->assertObjectNotHasProperty("detail", $decodedPayload);
        $this->assertSame("Exception", $decodedPayload->msg);
        $this->assertSame(ClientErrorCodes::NOT_FOUND->value, $decodedPayload->code);
    }

    #[Test]
    public function itHandlesAnInternalError(): void
    {
        $handler = new JsonErrorHandler(new StatusCodeFactory());
        
        $response = $handler->handle(new \ErrorException("Test", ClientErrorCodes::NOT_FOUND->value));
        $payload = $response->body();
        $decodedPayload = json_decode($payload);

        $this->assertSame("text/json", $response->contentType());
        $this->assertSame(ClientErrorCodes::NOT_FOUND, $response->responseCode());

        $this->assertJson($payload);
        $this->assertObjectHasProperty("msg", $decodedPayload);
        $this->assertObjectHasProperty("code", $decodedPayload);
        $this->assertObjectNotHasProperty("detail", $decodedPayload);
        $this->assertSame("Internal Error", $decodedPayload->msg);
        $this->assertSame(ClientErrorCodes::NOT_FOUND->value, $decodedPayload->code);
    }

    #[Test]
    public function itHandlesAnErrorWithDetails(): void
    {
        $handler = new JsonErrorHandler(statusCodeFactory: new StatusCodeFactory(), exposeDetails: true);
        
        $response = $handler->handle(new \Exception("Test", ClientErrorCodes::NOT_FOUND->value));
        $payload = $response->body();
        $decodedPayload = json_decode($payload);

        $this->assertSame("text/json", $response->contentType());
        $this->assertSame(ClientErrorCodes::NOT_FOUND, $response->responseCode());

        $this->assertJson($payload);
        $this->assertObjectHasProperty("msg", $decodedPayload);
        $this->assertObjectHasProperty("code", $decodedPayload);
        $this->assertObjectHasProperty("detail", $decodedPayload);
        $this->assertSame("Exception", $decodedPayload->msg);
        $this->assertSame(ClientErrorCodes::NOT_FOUND->value, $decodedPayload->code);
        $this->assertSame("Exception: Test", $decodedPayload->detail);
    }
}
