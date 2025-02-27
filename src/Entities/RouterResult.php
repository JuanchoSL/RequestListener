<?php declare(strict_types=1);

namespace JuanchoSL\RequestListener\Entities;

use JuanchoSL\RequestListener\Contracts\RouterResultInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RouterResult implements RouterResultInterface
{
    protected bool $success = false;
    protected RequestHandlerInterface $handler;

    public function isSuccess(): bool
    {
        return $this->success || !empty($this->handler);
    }

    public function getHandler(): RequestHandlerInterface
    {
        return $this->handler;
    }

    public function setHandler(RequestHandlerInterface $handler)
    {
        $this->handler = $handler;
        return $this;
    }
}