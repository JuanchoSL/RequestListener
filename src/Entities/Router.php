<?php declare(strict_types=1);

namespace JuanchoSL\RequestListener\Entities;

use JuanchoSL\RequestListener\Contracts\MiddlewareableInterface;
use JuanchoSL\RequestListener\Contracts\RouterInterface;
use JuanchoSL\RequestListener\Contracts\RouterResultInterface;
use JuanchoSL\RequestListener\Handlers\QueueMiddlewaresHandler;
use JuanchoSL\RequestListener\Handlers\RequestHandler;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Router implements RouterInterface, MiddlewareableInterface
{
    protected string $target = "";
    protected string $method = "";
    protected $call;
    protected RequestHandlerInterface $handler;

    protected array $middlewares = [];


    public function __construct($method, $target, $call)
    {
        $this->method = $method;
        $this->target = $target;
        $this->call = $call;
    }
    public function getTarget(): string
    {
        return $this->target;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function addMiddleware(MiddlewareInterface $middleware): MiddlewareableInterface
    {
        $this->middlewares[] = $middleware;
        return $this;
    }

    public function match(ServerRequestInterface $request): RouterResultInterface
    {
        $arguments = [];
        $router_result = new RouterResult();
        if ($request->getMethod() == $this->method) {
            if (preg_match('~^' . preg_replace('~/:(\w+)~', '/(?<$1>\w+)', $this->target) . '$~i', $request->getRequestTarget(), $results)) {
                foreach ($results as $name => $result) {
                    if (!is_numeric($name)) {
                        $request = $request->withAttribute($name, $result);
                        $arguments[$name] = $result;
                    }
                }
                $request = $request->withRequestTarget($this->target);
                $handler = new QueueMiddlewaresHandler(new RequestHandler($this->call, $arguments));
                foreach (array_reverse($this->middlewares) as $middleware) {
                    $handler->addMiddleware($middleware);
                }
                $router_result->setHandler($handler);
            }
        }
        return $router_result;
    }
}