<?php

declare(strict_types=1);

namespace App\Middleware;

use Framework\Http\Request;
use Framework\Http\RequestHandlerInterface;
use Framework\Http\Response;
use Framework\MiddlewareInterface;

class ChangeRequest implements MiddlewareInterface
{
    public function process(Request $request, RequestHandlerInterface $next): Response
    {
        $request->post = array_map('trim', $request->post);

        $response = $next->handle($request);

        return $response;
    }
}
