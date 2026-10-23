<?php

namespace Framework;

use Framework\Http\Request;
use Framework\Http\RequestHandlerInterface;
use Framework\Http\Response;

interface MiddlewareInterface
{
    public function process(Request $request, RequestHandlerInterface $next): Response;
}
