<?php declare(strict_types=1);

namespace JuanchoSL\RequestListener\Entities;

use JuanchoSL\RequestListener\Contracts\MiddlewareableInterface;
use Psr\Http\Server\MiddlewareInterface;

class RouterGroup implements MiddlewareableInterface
{
    protected array $elements = [];

    public function add(MiddlewareableInterface $element): MiddlewareableInterface
    {
        $this->elements[] = $element;
        return $this;
    }
    public function addMiddleware(MiddlewareInterface $middleware): MiddlewareableInterface
    {
        foreach ($this->elements as $element) {
            $element->addMiddleware($middleware);
        }
        return $this;
    }

}