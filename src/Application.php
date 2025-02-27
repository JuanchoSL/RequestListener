<?php declare(strict_types=1);

namespace JuanchoSL\RequestListener;

use JuanchoSL\RequestListener\Contracts\EnginesInterface;
use JuanchoSL\RequestListener\Contracts\ErrorHandlerInterface;
use JuanchoSL\RequestListener\Contracts\MiddlewareableInterface;
use JuanchoSL\RequestListener\Contracts\UseCaseInterface;
use JuanchoSL\RequestListener\Entities\Router;
use JuanchoSL\RequestListener\Entities\RouterGroup;
use JuanchoSL\RequestListener\Enums\OptionsEnum;
use JuanchoSL\RequestListener\Handlers\NotAllowedResponseHandler;
use JuanchoSL\RequestListener\Handlers\QueueRequestHandler;
use JuanchoSL\RequestListener\Middlewares\HeadMethodMiddleware;
use JuanchoSL\RequestListener\Middlewares\OptionsMethodMiddleware;
use JuanchoSL\RequestListener\Middlewares\TraceMethodMiddleware;
use JuanchoSL\RequestListener\Middlewares\ValidRouteMiddleware;
use JuanchoSL\RequestListener\Engines\ConsoleEngine;
use JuanchoSL\RequestListener\Engines\WebEngine;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class Application implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected ?RouterGroup $group = null;
    protected string $basePattern = '';
    protected ErrorHandlerInterface $error_handler;
    protected EnginesInterface $stream;
    protected QueueRequestHandler $main_handler;

    protected bool $debug = false;

    public function __construct(?EnginesInterface $engine = null)
    {
        if (!is_null($engine)) {
            $this->stream = $engine;
        } elseif (PHP_SAPI == 'cli') {
            $this->stream = ConsoleEngine::parse(array_slice($_SERVER['argv'], 1 + 1) ?? []);
        } else {
            $this->stream = WebEngine::parse($_REQUEST ?? []);
        }
        $this->main_handler = new QueueRequestHandler(new NotAllowedResponseHandler);
        $this->addMiddleware(new ValidRouteMiddleware);
        $this->addMiddleware(new OptionsMethodMiddleware);
        $this->addMiddleware(new TraceMethodMiddleware);
        $this->addMiddleware(new HeadMethodMiddleware);
        //$this->addMiddleware(new ValidMethodMiddleware);
        //$this->addMiddleware(new ValidMediaTypeMiddleware);

    }
    public function addMiddleware(MiddlewareInterface $middleware): MiddlewareableInterface
    {
        return $this->main_handler->addMiddleware($middleware);
    }

    public function handlerException(\Throwable $error, $context = [])
    {
        $this->stream->sendMessage(call_user_func_array($this->error_handler, [$this->stream->getRequest(), $error]));
    }

    public function setErrorHandler(ErrorHandlerInterface $error_handler): static
    {
        set_exception_handler([$this, 'handlerException']);
        $this->error_handler = $error_handler;
        return $this;
    }

    public function setDebug(bool $debug = false): static
    {
        $this->debug = $debug;
        return $this;
    }

    public function cli(string $alias, UseCaseInterface|callable|string|array $command)
    {
        return $this->add($alias, $command, OptionsEnum::CLI);
    }

    public function get(string $alias, UseCaseInterface|callable|string|array $command)
    {
        return $this->add($alias, $command, OptionsEnum::GET);
    }

    public function post(string $alias, UseCaseInterface|callable|string|array $command)
    {
        return $this->add($alias, $command, OptionsEnum::POST);
    }

    public function put(string $alias, UseCaseInterface|callable|string|array $command)
    {
        return $this->add($alias, $command, OptionsEnum::PUT);
    }

    public function patch(string $alias, UseCaseInterface|callable|string|array $command)
    {
        return $this->add($alias, $command, OptionsEnum::PATCH);
    }
    public function delete(string $alias, UseCaseInterface|callable|string|array $command)
    {
        return $this->add($alias, $command, OptionsEnum::DELETE);
    }

    protected function add(string $alias, UseCaseInterface|callable|string|array $command, OptionsEnum ...$valid_options)
    {
        foreach ($valid_options as $option) {
            $route = new Router(strtoupper($option->value), $this->basePattern . $alias, $command);
            $route = $this->main_handler->add($route);
            if (!empty($this->group)) {
                $this->group->add($route);
            }
            return $route;
        }
    }

    public function group($pattern, \Closure $callable)
    {
        $oldBasePattern = $this->basePattern;
        $group = $this->group;
        $this->basePattern .= $pattern;
        $this->group = $new_group = new RouterGroup();
        if (is_callable($callable)) {
            call_user_func($callable);
        }
        $this->group = $group;
        if (!empty($this->group)) {
            $this->group->add($new_group);
        }
        $this->basePattern = $oldBasePattern;
        return $new_group;
    }

    public function run(): void
    {
        $this->stream->sendMessage($this->main_handler->handle($this->stream->getRequest()));
    }
}