<?php declare(strict_types=1);

namespace JuanchoSL\RequestListener\Contracts;

use Psr\Http\Message\ServerRequestInterface;

interface RouterInterface
{
    public function getTarget(): string;
    public function getMethod(): string;
    public function match(ServerRequestInterface &$request): RouterResultInterface;
}