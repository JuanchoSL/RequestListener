<?php declare(strict_types=1);

namespace JuanchoSL\RequestListener\Middlewares;

use Fig\Http\Message\RequestMethodInterface;
use Fig\Http\Message\StatusCodeInterface;
use JuanchoSL\HttpData\Factories\ResponseFactory;
use JuanchoSL\HttpData\Factories\StreamFactory;
use JuanchoSL\RequestListener\Contracts\RouterInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RoutingMiddleware implements MiddlewareInterface
{
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $result = $this->router->match($request);
        
        if ($result->isSuccess()) {
            return $result->getHandler()->handle($request);
            if ($request->getMethod() == RequestMethodInterface::METHOD_OPTIONS) {
                $response = (new ResponseFactory)->createResponse(StatusCodeInterface::STATUS_NO_CONTENT)
                ->withHeader('Allow', array_keys($handler->routes[$request->getRequestTarget()]))
                    ->withBody((new StreamFactory)->createStream());
            } else {
                $response = $result->getHandler()->handle($request);
                if ($request->getMethod() == RequestMethodInterface::METHOD_HEAD) {
                    $response = $response->withBody((new StreamFactory)->createStream());
                }
            }
            return $response;
        } else {
            return $handler->handle($request);
        }
    }
}