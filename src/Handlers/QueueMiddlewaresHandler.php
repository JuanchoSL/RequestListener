<?php declare(strict_types=1);

namespace JuanchoSL\RequestListener\Handlers;

use JuanchoSL\RequestListener\Contracts\MiddlewareableInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class QueueMiddlewaresHandler implements RequestHandlerInterface, MiddlewareableInterface
{
    private $middleware = [];
    private $fallbackHandler;

    public function __construct(RequestHandlerInterface $fallbackHandler)
    {
        $this->fallbackHandler = $fallbackHandler;
    }

    public function addMiddleware(MiddlewareInterface $middleware): static
    {
        $this->middleware[] = $middleware;
        return $this;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (0 === count($this->middleware)) {
            return $this->fallbackHandler->handle($request);
        }
        $middleware = array_shift($this->middleware);
        return $middleware->process($request, $this);
    }
}