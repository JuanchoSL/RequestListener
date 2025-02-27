<?php declare(strict_types=1);

namespace JuanchoSL\RequestListener\Contracts;

use Psr\Http\Server\MiddlewareInterface;

interface MiddlewareableInterface
{
    public function addMiddleware(MiddlewareInterface $middleware): MiddlewareableInterface;
}