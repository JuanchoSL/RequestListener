<?php declare(strict_types=1);

namespace JuanchoSL\RequestListener\Middlewares;

use Fig\Http\Message\RequestMethodInterface;
use Fig\Http\Message\StatusCodeInterface;
use JuanchoSL\HttpData\Factories\ResponseFactory;
use JuanchoSL\HttpData\Factories\StreamFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class OptionsMethodMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($request->getMethod() == RequestMethodInterface::METHOD_OPTIONS) {
            return (new ResponseFactory)->createResponse(StatusCodeInterface::STATUS_NO_CONTENT)->withHeader('Allow', array_keys($request->getAttribute('commands')[$request->getRequestTarget()]))->withBody((new StreamFactory)->createStream());
        }
        return $handler->handle($request);
    }
}