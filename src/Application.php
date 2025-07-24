<?php declare(strict_types=1);

namespace JuanchoSL\RequestListener;

use JuanchoSL\HttpData\Factories\ServerRequestFactory;
use JuanchoSL\RequestListener\Commands\HelpCommands;
use JuanchoSL\RequestListener\Contracts\EnginesInterface;
use JuanchoSL\RequestListener\Contracts\MiddlewareableInterface;
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
use JuanchoSL\RequestListener\Traits\AppendMethodsTrait;
use JuanchoSL\RequestListener\Traits\ErrorControlTrait;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class Application implements LoggerAwareInterface, ClientInterface
{
    use LoggerAwareTrait, AppendMethodsTrait, ErrorControlTrait;

    protected static Application $instance;
    protected EnginesInterface $stream;
    protected QueueRequestHandler $main_handler;
    protected array $body_parser = [];
    protected bool $debug = false;

    public static function getInstance(?EnginesInterface $engine = null)
    {
        if (empty(static::$instance)) {
            static::$instance = new self($engine);
        }
        return static::$instance;
    }
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
        $this->addBodyParser('text/xml', XmlDataTransfer::class);
        $this->addBodyParser('application/xml', XmlDataTransfer::class);
        $this->addBodyParser('text/csv', CsvDataTransfer::class);
        $this->addBodyParser('application/csv', ExcelCsvDataTransfer::class);
        $this->addBodyParser('application/x-www-form-urlencoded', ArrayDataTransfer::class);
        $this->addBodyParser('multipart/form-data', ArrayDataTransfer::class);
    }
    public function addBodyParser(string $media_type, string|array|callable $parser): static
    {
        $this->body_parser[$media_type] = $parser;
        return $this;
    }
    public function addMiddleware(MiddlewareInterface $middleware): MiddlewareableInterface
    {
        return $this->main_handler->addMiddleware($middleware);
    }

    public function setDebug(bool $debug = false): static
    {
        $this->debug = $debug;
        return $this;
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        return $this->handle((new ServerRequestFactory)->fromRequest($request));
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ($request->getBody()->getSize() > 0) {
            $body = (empty($request->getParsedBody())) ? (string) $request->getBody() : $request->getParsedBody();
            $body = trim($body, " \r\n");
            if (array_key_exists(current($request->getHeader('content-type')), $this->body_parser)) {
                $parser = $this->body_parser[current($request->getHeader('content-type'))];
                if (is_array($parser)) {
                    $body = call_user_func($parser, $body);
                } elseif (is_object($parser)) {
                    $body = $parser($body);
                } else {
                    $body = new $parser($body);
                }
                $request = $request->withParsedBody($body);
            }
        }
        return $this->main_handler->handle($request);
    }

    public function runWithoutExit()
    {
        $request = $this->stream->getRequest();
        //$this->get('/help', (new HelpCommands)->setCommands($this->main_handler->getRoutes()));
        return $this->stream->sendMessage($this->handle($request));
    }

    public function run(int $limit_code = 400)
    {
        return exit(max(0, $this->runWithoutExit() - ($limit_code - 1)));
    }
}