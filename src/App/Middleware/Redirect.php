<?php

declare(strict_types=1);

namespace App\Middleware;

use Framework\Http\Request;
use Framework\Http\RequestHandlerInterface;
use Framework\Http\Response;
use Framework\MiddlewareInterface;

class Redirect implements MiddlewareInterface
{

    public function __construct(
        private Response $response
    ) {}
    public function process(Request $request, RequestHandlerInterface $next): Response
    {

        $this->response->redirect('/products');

        return $this->response;
    }
}
