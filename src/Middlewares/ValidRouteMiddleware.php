<?php declare(strict_types=1);

namespace JuanchoSL\RequestListener\Middlewares;

use Fig\Http\Message\StatusCodeInterface;
use JuanchoSL\HttpData\Factories\ResponseFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ValidRouteMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $request = $request->withRequestTarget("/" . ltrim($request->getRequestTarget(), '/ '));
        foreach ($handler->routes as $target => $content) {
            if (preg_match('~^' . preg_replace('~/:(\w+)~', '/(?<$1>\w+)', $target) . '$~i', $request->getRequestTarget(), $results)) {
                return $handler->handle($request);
            }
        }
        return (new ResponseFactory)
            ->createResponse(StatusCodeInterface::STATUS_NOT_FOUND)
            ->withProtocolVersion($request->getProtocolVersion());
    }
}