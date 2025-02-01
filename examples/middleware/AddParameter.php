<?php

declare(strict_types=1);

use Docnet\JAPI\controller\RequestHandlerInterface;
use Docnet\JAPI\middleware\MiddlewareInterface;
use gordonmcvey\httpsupport\enum\statuscodes\SuccessCodes;
use gordonmcvey\httpsupport\RequestInterface;
use gordonmcvey\httpsupport\Response;
use gordonmcvey\httpsupport\ResponseInterface;

class AddParameter implements MiddlewareInterface
{
    public function __construct(private readonly string $key, private readonly string $value) {}

    public function handle(RequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        error_log(message: sprintf("%s: %s, %s", __METHOD__, $this->key, $this->value));

        $response = $handler->dispatch($request);
        $payload = json_decode($response->body());
        $payload->{$this->key} = $this->value;

        return new Response(SuccessCodes::OK, (string) json_encode($payload, JSON_PRETTY_PRINT));
    }
}
