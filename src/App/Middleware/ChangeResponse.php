<?php

declare(strict_types=1);

namespace App\Middleware;

use Framework\Http\Request;
use Framework\Http\RequestHandlerInterface;
use Framework\Http\Response;
use Framework\MiddlewareInterface;

class ChangeResponse implements MiddlewareInterface
{
    public function process(Request $request, RequestHandlerInterface $next): Response
    {
        $response = $next->handle($request);

        $response->setBody($response->getBody() . ' message from the ChangeResponse middleware');

        return $response;
    }
}
