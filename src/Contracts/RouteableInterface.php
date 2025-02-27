<?php declare(strict_types=1);

namespace JuanchoSL\RequestListener\Contracts;

use Psr\Http\Server\RequestHandlerInterface;

interface RouteableInterface extends RequestHandlerInterface
{
    public function getRoutes(): iterable;
    public function add(RouterInterface $routerInterface): RouterInterface;
}