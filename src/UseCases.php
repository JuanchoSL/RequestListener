<?php

declare(strict_types=1);

namespace JuanchoSL\RequestListener;

use JuanchoSL\DataTransfer\Factories\DataTransferFactory;
use JuanchoSL\DataTransfer\Repositories\ArrayDataTransfer;
use JuanchoSL\Exceptions\PreconditionFailedException;
use JuanchoSL\Exceptions\PreconditionRequiredException;
use JuanchoSL\HttpData\Factories\ResponseFactory;
use JuanchoSL\RequestListener\Commands\HelpCommand;
use JuanchoSL\RequestListener\Contracts\UseCaseInterface;
use JuanchoSL\RequestListener\Entities\InputImmutable;
use JuanchoSL\RequestListener\Enums\InputArgument;
use JuanchoSL\RequestListener\Enums\InputOption;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerAwareTrait;

abstract class UseCases implements UseCaseInterface, RequestHandlerInterface
{

    use LoggerAwareTrait;

    /**
     * Summary of arguments
     * @var array<string, array<string, InputArgument|InputOption>>
     */
    protected array $arguments = [];
    protected bool $debug = false;

    protected ResponseInterface $response;
    public function setDebug(bool $debug = false): static
    {
        $this->debug = $debug;
        return $this;
    }

    protected function log(\Stringable|string $message, $log_level, array $context = []): void
    {
        if (isset($this->logger)) {
            if ($this->debug || $log_level != 'debug') {
                $context['memory'] = memory_get_usage();
                $this->logger->log($log_level, $message, $context);
            }
        }
    }

    protected function validate(ContainerInterface $vars): void
    {
        foreach ($this->arguments as $name => $argument) {
            if ($argument['argument'] == InputArgument::REQUIRED && !$vars->has($name)) {
                $exception = new PreconditionRequiredException("The argument '{$name}' is missing");
            } elseif (in_array($argument['option'], [InputOption::SINGLE, InputOption::SINGLE_INT]) && $vars->has($name) && is_iterable($vars->get($name))) {
                $exception = new PreconditionFailedException("The argument '{$name}' needs to be a single parameter");
            } elseif (
                $vars->has($name) && (
                    ($argument['option'] == InputOption::SINGLE_INT && empty(filter_var($vars->get($name), FILTER_VALIDATE_INT)))
                    ||
                    ($argument['option'] == InputOption::MULTI_INT && empty(array_filter(filter_var_array(json_decode(json_encode($vars->get($name)), true), FILTER_VALIDATE_INT | FILTER_FLAG_EMPTY_STRING_NULL))))
                )
            ) {
                $exception = new PreconditionFailedException("The argument '{$name}' needs to have an integer as value");
            }
            if (isset($exception)) {
                $this->log($exception, 'error', [
                    'exception' => $exception,
                    'parameters' => $vars,
                    'arguments' => $this->arguments
                ]);
                throw $exception;
            }
        }
    }

    public function addArgument(string $name, InputArgument $required, InputOption $option): void
    {
        $this->arguments[$name] = [
            'argument' => $required,
            'option' => $option
        ];
    }

    public function getArgument(string $name): mixed
    {
        return $this->arguments[$name];
    }

    public function handle(ServerRequestInterface $input): ResponseInterface
    {
        return $this->run($input, (new ResponseFactory)->createResponse());
    }

    public function run(ServerRequestInterface $input, ResponseInterface $response, ?string $method = null): ResponseInterface
    {
        if (!empty($params = $input->getQueryParams()) && is_iterable($params)) {
            $params = new ArrayDataTransfer($params);
            foreach ($params as $key => $value) {
                $input = $input->withAttribute($key, $value);
            }
        }
        if (!empty($body = $input->getParsedBody()) && is_iterable($body)) {
            foreach ($body as $key => $value) {
                $input = $input->withAttribute($key, $value);
            }
        }
        $time = time();
        $this->configure();
        if (array_key_exists('help', $input->getQueryParams())) {
            $response = (new HelpCommand())->__invoke($input->withAttribute('arguments', $this->arguments), $response);
        } else {
            if (!empty($input->getAttributes()) && !empty($this->arguments)) {
                $this->validate(new InputImmutable(DataTransferFactory::byTrasversable($input->getAttributes())));
            }
            $GLOBALS['request'] = $input;
            $response = (is_null($method)) ? $this($input, $response) : $this->$method($input, $response);
        }

        $this->log("Command: '{command}'", 'debug', [
            'command' => get_called_class(),//$this->getName(),
            'input' => $input,
            //'result' => $result,
            'cwd' => getcwd(),
            'time' => time() - $time
        ]);
        return $response;
    }

    abstract protected function configure(): void;
}
