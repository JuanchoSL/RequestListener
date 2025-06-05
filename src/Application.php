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
use JuanchoSL\DataTransfer\Repositories\ArrayDataTransfer;
use JuanchoSL\DataTransfer\Repositories\CsvDataTransfer;
use JuanchoSL\DataTransfer\Repositories\ExcelCsvDataTransfer;
use JuanchoSL\DataTransfer\Repositories\JsonDataTransfer;
use JuanchoSL\DataTransfer\Repositories\XmlDataTransfer;
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
    protected array $body_parser = [];
    protected bool $debug = false;

    public function __construct(?EnginesInterface $engine = null)
    {
        if (!is_null($engine)) {
            $this->stream = $engine;
        } elseif (PHP_SAPI == 'cli') {
            $this->stream = ConsoleEngine::parse();
        } else {
            $this->stream = WebEngine::parse();
        }
        $this->main_handler = new QueueRequestHandler(new NotAllowedResponseHandler);
        $this->addMiddleware(new ValidRouteMiddleware);
        $this->addMiddleware(new OptionsMethodMiddleware);
        $this->addMiddleware(new TraceMethodMiddleware);
        $this->addMiddleware(new HeadMethodMiddleware);
        //$this->addMiddleware(new ValidMethodMiddleware);
        //$this->addMiddleware(new ValidMediaTypeMiddleware);
        $this->addBodyParser('application/json', JsonDataTransfer::class);
        $this->addBodyParser('application/xml', XmlDataTransfer::class);
        $this->addBodyParser('text/csv', CsvDataTransfer::class);
        $this->addBodyParser('application/csv', ExcelCsvDataTransfer::class);
        $this->addBodyParser('application/x-www-form-urlencoded', ArrayDataTransfer::class);
        $this->addBodyParser('multipart/form-data', ArrayDataTransfer::class);
    }
    public function addBodyParser(string $media_type, $parser): static
    {
        $this->body_parser[$media_type] = $parser;
        return $this;
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

    public function cli(string $alias, UseCaseInterface|callable|string|array $command): MiddlewareableInterface
    {
        return $this->add($alias, $command, OptionsEnum::CLI);
    }

    public function get(string $alias, UseCaseInterface|callable|string|array $command): MiddlewareableInterface
    {
        return $this->add($alias, $command, OptionsEnum::GET);
    }

    public function post(string $alias, UseCaseInterface|callable|string|array $command): MiddlewareableInterface
    {
        return $this->add($alias, $command, OptionsEnum::POST);
    }

    public function put(string $alias, UseCaseInterface|callable|string|array $command): MiddlewareableInterface
    {
        return $this->add($alias, $command, OptionsEnum::PUT);
    }

    public function patch(string $alias, UseCaseInterface|callable|string|array $command): MiddlewareableInterface
    {
        return $this->add($alias, $command, OptionsEnum::PATCH);
    }
    public function delete(string $alias, UseCaseInterface|callable|string|array $command): MiddlewareableInterface
    {
        return $this->add($alias, $command, OptionsEnum::DELETE);
    }

    protected function add(string $alias, UseCaseInterface|callable|string|array $command, OptionsEnum ...$valid_options): ?MiddlewareableInterface
    {
        foreach ($valid_options as $option) {
            $route = new Router(strtoupper($option->value), $this->basePattern . $alias, $command);
            $route = $this->main_handler->add($route);
            if (!empty($this->group)) {
                $this->group->add($route);
            }
            return $route;
        }
        return null;
    }

    public function group($pattern, \Closure $callable): MiddlewareableInterface
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

    public function runWithoutExit()
    {
        $request = $this->stream->getRequest();
        if ($request->getBody()->getSize() > 0) {
            $body = (empty($request->getParsedBody())) ? (string) $request->getBody() : $request->getParsedBody();
            if (array_key_exists(current($request->getHeader('content-type')), $this->body_parser)) {
                $parser = $this->body_parser[current($request->getHeader('content-type'))];
                $body = new $parser($body);
                $request = $request->withParsedBody($body);
            }
        }
        return $this->stream->sendMessage($this->main_handler->handle($request));
    }
    public function run(int $limit_code = 400)
    {
        return exit(max($limit_code, $this->runWithoutExit()));
    }
}