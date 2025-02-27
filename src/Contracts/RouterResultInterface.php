<?php declare(strict_types=1);

namespace JuanchoSL\RequestListener\Contracts;

use Psr\Http\Server\RequestHandlerInterface;

interface RouterResultInterface
{
    public function isSuccess(): bool;
    public function getHandler(): RequestHandlerInterface;
}