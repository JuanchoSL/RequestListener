<?php declare(strict_types=1);

namespace JuanchoSL\RequestListener\Handlers;

use JuanchoSL\HttpData\Factories\ResponseFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RequestHandler implements RequestHandlerInterface
{

    protected $command;
    protected $arguments = [];

    public function __construct(callable|array|string $command, $arguments = [])
    {
        $this->command = $command;
        $this->arguments = $arguments;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $response = (new ResponseFactory)->createResponse()->withProtocolVersion($request->getProtocolVersion());

        $command = $this->command;
        $function = null;
        if (is_array($command)) {
            list($command, $function) = $command;
        }
        if (!($command instanceof UseCaseInterface)) {
            $command = new $command;
        }
        if (!empty($function)) {
            $command = [$command, $function];
        }
        return $response = call_user_func_array($command, [$request, $response, $this->arguments]);
    }
}