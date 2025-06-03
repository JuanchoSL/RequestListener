<?php declare(strict_types=1);

namespace JuanchoSL\RequestListener\Contracts;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;


interface EnginesInterface
{

    public static function parse(): EnginesInterface;
    public function getRequest(): ServerRequestInterface;
    public function sendMessage(ResponseInterface $response);
}