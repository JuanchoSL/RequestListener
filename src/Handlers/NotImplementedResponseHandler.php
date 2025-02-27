<?php declare(strict_types=1);

namespace JuanchoSL\RequestListener\Handlers;

use Fig\Http\Message\StatusCodeInterface;
use JuanchoSL\HttpData\Factories\ResponseFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class NotImplementedResponseHandler implements RequestHandlerInterface
{

    public function handle(ServerRequestInterface $server_request): ResponseInterface
    {
        return (new ResponseFactory)
            ->createResponse(StatusCodeInterface::STATUS_NOT_IMPLEMENTED)
            ->withProtocolVersion($server_request->getProtocolVersion());
    }
}