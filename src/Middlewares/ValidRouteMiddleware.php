<?php declare(strict_types=1);

namespace JuanchoSL\RequestListener\Middlewares;

use Fig\Http\Message\StatusCodeInterface;
use JuanchoSL\HttpData\Factories\ResponseFactory;
use JuanchoSL\HttpData\Factories\StreamFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ValidRouteMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $request = $request->withRequestTarget("/" . ltrim($request->getRequestTarget(), '/ '));
        foreach ($handler->routes as $target => $contents) {
            if (current($contents)->checkTarget($request->getRequestTarget())) {
                return $handler->handle($request);
            }
        }
        return (new ResponseFactory)
            ->createResponse(StatusCodeInterface::STATUS_NOT_FOUND)
            ->withBody((new StreamFactory)->createStream("The target {$request->getUri()->getPath()} does not exists"))
            ->withProtocolVersion($request->getProtocolVersion());
    }
}