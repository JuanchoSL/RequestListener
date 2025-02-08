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

class TraceMethodMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($request->getMethod() == RequestMethodInterface::METHOD_TRACE) {
            $body = $request->getMethod() . " " . $request->getRequestTarget() . " HTTP/" . $request->getProtocolVersion() . "\r\n";
            foreach ($request->withoutHeader('accept')->getHeaders() as $name => $value) {
                $body .= $name . ": " . $request->getHeaderLine($name) . "\r\n";
            }
            return (new ResponseFactory)
                ->createResponse(StatusCodeInterface::STATUS_OK)
                ->withHeader('Content-type', 'message/http')
                ->withBody((new StreamFactory)->createStream($body));
        }
        return $handler->handle($request);
    }
}