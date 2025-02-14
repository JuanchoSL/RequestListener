<?php declare(strict_types=1);

namespace JuanchoSL\RequestListener\Handlers;

use JuanchoSL\DataTransfer\Factories\DataTransferFactory;
use JuanchoSL\RequestListener\Entities\InputImmutable;
use JuanchoSL\HttpData\Factories\ResponseFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RunnerRequestHandler implements RequestHandlerInterface
{
/*
    public function handler(ServerRequestInterface $request): ResponseInterface
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

        $time = time();
        $command->configure();
        if (array_key_exists('help', $request->getQueryParams())) {
            //$response = (new HelpCommand())->__invoke($request->withAttribute('arguments', $this->arguments), $response);
        } else {
            $command->validate(new InputImmutable(DataTransferFactory::byTrasversable($request->getQueryParams())));
            $response = (is_null($function)) ? $command($request, $response) : $command->$function($request, $response);
        }

        $this->log("Command: '{command}'", 'debug', [
            'command' => get_called_class(),//$this->getName(),
            'input' => $request,
            //'result' => $result,
            'cwd' => getcwd(),
            'time' => time() - $time
        ]);
        return $response->withHeader('Content-Length', (string) $response->getBody()->getSize());
    }*/

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
        //return $response->withHeader('Content-Length', (string) $response->getBody()->getSize());
    }
}