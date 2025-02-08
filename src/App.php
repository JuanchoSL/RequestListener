<?php declare(strict_types=1);

namespace JuanchoSL\RequestListener;

use JuanchoSL\RequestListener\Commands\HelpCommands;
use JuanchoSL\RequestListener\Contracts\EnginesInterface;
use JuanchoSL\RequestListener\Contracts\ErrorHandlerInterface;
use JuanchoSL\RequestListener\Contracts\UseCaseInterface;
use JuanchoSL\RequestListener\Enums\OptionsEnum;
use JuanchoSL\RequestListener\Handlers\MiddlewaresHandler;
use JuanchoSL\RequestListener\Handlers\RunnerRequestHandler;
use JuanchoSL\RequestListener\Middlewares\OptionsMethodMiddleware;
use JuanchoSL\RequestListener\Middlewares\RunnerMiddleware;
use JuanchoSL\RequestListener\Middlewares\TraceMethodMiddleware;
use JuanchoSL\RequestListener\Middlewares\ValidMediaTypeMiddleware;
use JuanchoSL\RequestListener\Middlewares\ValidMethodMiddleware;
use JuanchoSL\RequestListener\Middlewares\ValidRouteMiddleware;
use JuanchoSL\RequestListener\Engines\ConsoleEngine;
use JuanchoSL\RequestListener\Engines\WebEngine;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class App implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * Summary of commands
     * @var array<string, array<string, mixed>>
     */
    protected array $commands = [];
    protected array $middlewares = [];


    protected bool $debug = false;
    protected ErrorHandlerInterface $error_handler;
    protected EnginesInterface $stream;
    protected RequestHandlerInterface $request_handler;
    protected ResponseInterface $response_handler;

    protected bool $displayErrorDetails = false;
    protected bool $logErrors = false;
    protected bool $logErrorDetails = false;

    public function __construct(?EnginesInterface $engine = null)
    {
        if (!is_null($engine)) {
            $this->stream = $engine;
        } elseif (PHP_SAPI == 'cli') {
            $this->stream = ConsoleEngine::parse(array_slice($_SERVER['argv'], 1 + 1) ?? []);
        } else {
            $this->stream = WebEngine::parse($_REQUEST ?? []);
        }
        $this->middlewares[] = new ValidRouteMiddleware;
        $this->middlewares[] = new OptionsMethodMiddleware;
        $this->middlewares[] = new TraceMethodMiddleware;
        $this->middlewares[] = new ValidMethodMiddleware;
        $this->middlewares[] = new ValidMediaTypeMiddleware;

    }
    public function addMiddleware(MiddlewareInterface $middleware)
    {
        $this->middlewares[] = $middleware;
    }
    public function handlerException(\Throwable $error, $context = [])
    {
        $this->stream->sendMessage(call_user_func_array($this->error_handler, [$this->stream->getRequest(), $error]));
        //$this->stream->sendMessage(call_user_func_array($this->error_handler, [$this->stream->getRequest(), $error, $this->displayErrorDetails, $this->logErrors, $this->logErrorDetails]));
        //return call_user_func_array($this->error_handler, [$this->stream->getRequest(), $error, $this->displayErrorDetails, $this->logErrors, $this->logErrorDetails]);
    }

    public function setErrorHandler(ErrorHandlerInterface $error_handler): static
    {
        set_exception_handler([$this, 'handlerException']);
        $this->error_handler = $error_handler;
        return $this;
    }

    public function setRequestHandler(RequestHandlerInterface $request_handler): static
    {
        $this->request_handler = $request_handler;
        return $this;
    }


    public function setDebug(bool $debug = false): static
    {
        $this->debug = $debug;
        return $this;
    }

    public function cli(string $alias, UseCaseInterface|callable|string|array $command): void
    {
        $this->add($alias, $command, OptionsEnum::CLI);
    }

    public function add(string $alias, UseCaseInterface|callable|string|array $command, OptionsEnum ...$valid_options): void
    {
        foreach ($valid_options as $option) {
            $this->commands[$alias][$option->value] = ['command' => $command];
        }
    }

    public function run(): void
    {
        $this->add('/help', new HelpCommands(), OptionsEnum::GET, OptionsEnum::CLI);

        //$this->middlewares[] = new RunnerMiddleware;
        $response = new MiddlewaresHandler($this->request_handler ?? new RunnerRequestHandler, ...$this->middlewares);
        $this->stream->sendMessage($response->handle($this->stream->getRequest()->withAttribute('commands', $this->commands)));
    }
}