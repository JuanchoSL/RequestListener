<?php declare(strict_types=1);

namespace JuanchoSL\RequestListener\Handlers;

use JuanchoSL\HttpData\Factories\ResponseFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RunnerRequestHandler implements RequestHandlerInterface
{

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $response = (new ResponseFactory)->createResponse()->withProtocolVersion($request->getProtocolVersion());

        $command = $request->getAttribute('commands')[$request->getRequestTarget()][$request->getMethod()]['command'];
        $function = null;
        if (is_array($command)) {
            list($command, $function) = $command;
        }
        if (!($command instanceof UseCaseInterface)) {
            $command = new $command;
        }
        return $response = call_user_func_array([$command, 'run'], [$request, $response, $function]);
    }
}