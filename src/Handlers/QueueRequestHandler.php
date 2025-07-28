<?php declare(strict_types=1);

namespace JuanchoSL\RequestListener\Handlers;

use JuanchoSL\RequestListener\Contracts\RouteableInterface;
use JuanchoSL\RequestListener\Contracts\RouterInterface;
use JuanchoSL\RequestListener\Middlewares\RoutingMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class QueueRequestHandler extends QueueMiddlewaresHandler implements RouteableInterface
{
    private $paths = [];

    public $routes = [];

    public function add(RouterInterface $router): RouterInterface
    {
        $this->routes[$router->getTarget()][$router->getMethod()] = $router;
        $this->paths[] = new RoutingMiddleware($router);
        return $router;
    }
    public function getRoutes(): array|\Traversable
    {
        return $this->routes;
    }
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (!empty($this->paths)) {
            foreach ($this->paths as $path) {
                $this->addMiddleware($path);
            }
            $this->paths = [];
        }
        return parent::handle($request);
    }
}