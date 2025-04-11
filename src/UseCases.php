<?php

declare(strict_types=1);

namespace JuanchoSL\RequestListener;

use JuanchoSL\DataTransfer\Factories\DataTransferFactory;
use JuanchoSL\Exceptions\PreconditionRequiredException;
use JuanchoSL\RequestListener\Commands\HelpCommand;
use JuanchoSL\RequestListener\Contracts\UseCaseInterface;
use JuanchoSL\RequestListener\Entities\InputImmutable;
use JuanchoSL\RequestListener\Enums\InputArgument;
use JuanchoSL\RequestListener\Enums\InputOption;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerAwareTrait;

abstract class UseCases implements UseCaseInterface
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

    protected function log(\Stringable|string $message, $log_level, $context = []): void
    {
        if (isset($this->logger)) {
            if ($this->debug || $log_level != 'debug') {
                $context['memory'] = memory_get_usage();
                if (!array_key_exists('command', $context)) {
                    $context['command'] = implode(' ', $_SERVER['argv']);
                }
                $this->logger->log($log_level, $message, $context);
            }
        }
    }

    protected function validate(ContainerInterface $vars): void
    {
        foreach ($this->arguments as $name => $argument) {
            if ($argument['argument'] == InputArgument::REQUIRED && !$vars->has($name)) {
                $exception = new PreconditionRequiredException("The argument '{$name}' is missing");
            } elseif ($argument['option'] == InputOption::SINGLE && $vars->has($name) && is_iterable($vars->get($name))) {
                $exception = new PreconditionRequiredException("The argument '{$name}' is a single parameter");
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

    public function run(ServerRequestInterface $input, ResponseInterface $response, ?string $method = null): ResponseInterface
    {
        $time = time();
        $this->configure();
        if (array_key_exists('help', $input->getQueryParams())) {
            $response = (new HelpCommand())->__invoke($input->withAttribute('arguments', $this->arguments), $response);
        } else {
            $this->validate(new InputImmutable(DataTransferFactory::byTrasversable($input->getAttributes())));
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
