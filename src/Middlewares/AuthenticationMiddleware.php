<?php

declare(strict_types=1);

namespace JuanchoSL\RequestListener\Middlewares;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;

class AuthenticationMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $handler->handle($request);
        
        echo "<pre>" . print_r($request->getServerParams(), true)."</pre>";exit;
        echo "<pre>" . print_r($_REQUEST, true)."</pre>";exit;
        $_SERVER['PHP_AUTH_USER'];
        $_SERVER['PHP_AUTH_PW'];

        $response = $handler->handle($request);
        return $response;
    }
}