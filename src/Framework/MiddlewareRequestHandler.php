<?php

declare(strict_types=1);


namespace Framework;

use Framework\Http\Request;
use Framework\Http\RequestHandlerInterface;
use Framework\Http\Response;
use Override;

class MiddlewareRequestHandler  implements RequestHandlerInterface
{
    public function __construct(
        private array $middlewares,
        private ControllerRequestHandler $controllerRequestHandler
    ) {}

    public function handle(Request $request): Response
    {

        $middleware = array_shift($this->middlewares);

        if ($middleware === null) {
            $response = $this->controllerRequestHandler->handle($request);
            return $response;
        }

        $response = $middleware->process($request, $this);

        return $response;
    }
}
