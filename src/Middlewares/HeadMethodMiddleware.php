<?php declare(strict_types=1);

namespace JuanchoSL\RequestListener\Middlewares;

use Fig\Http\Message\RequestMethodInterface;
use JuanchoSL\HttpData\Factories\StreamFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class HeadMethodMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $method = $request->getMethod();
        if ($method == RequestMethodInterface::METHOD_HEAD) {
            $request = $request->withMethod(RequestMethodInterface::METHOD_GET);
        }
        $response = $handler->handle($request);
        if ($method == RequestMethodInterface::METHOD_HEAD) {
            $response = $response->withHeader('Content-Length', (string) $response->getBody()->getSize())->withBody((new StreamFactory)->createStream(''));
        }
        return $response;
    }
}