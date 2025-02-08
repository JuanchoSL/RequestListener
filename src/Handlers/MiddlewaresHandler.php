<?php declare(strict_types=1);

namespace JuanchoSL\RequestListener\Handlers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class MiddlewaresHandler implements RequestHandlerInterface
{

    protected array $middlewares;
    protected RequestHandlerInterface $runner;

    public function __construct(RequestHandlerInterface $runner_handler, MiddlewareInterface ...$middlewares)
    {
        $this->runner = $runner_handler;
        $this->middlewares = $middlewares;
    }
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return (!empty($this->middlewares)) ? array_shift($this->middlewares)->process($request, $this) : $this->runner->handle($request);
    }
}