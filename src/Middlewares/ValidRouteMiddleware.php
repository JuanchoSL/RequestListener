<?php declare(strict_types=1);

namespace JuanchoSL\RequestListener\Middlewares;

use JuanchoSL\Exceptions\NotFoundException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ValidRouteMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $request = $request->withRequestTarget("/" . ltrim($request->getRequestTarget(), '/ '));

        foreach ($request->getAttribute('commands') as $target => $content) {
            if (preg_match('~^' . preg_replace('~/:(\w+)~', '/(?<$1>\w+)', $target) . '$~i', $request->getRequestTarget(), $results)) {
                foreach ($results as $name => $result) {
                    if (!is_numeric($name)) {
                        $request = $request->withAttribute($name, $result);
                    }
                }
                $request = $request->withRequestTarget($target);
                break;
            }
        }

        if (!array_key_exists($request->getRequestTarget(), $request->getAttribute('commands'))) {
            throw new NotFoundException(sprintf("The command '%s' is not defined", $request->getRequestTarget()));
        }
        return $handler->handle($request);
    }
}