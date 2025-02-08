<?php

declare(strict_types=1);

namespace JuanchoSL\RequestListener\Contracts;

use JuanchoSL\RequestListener\Enums\InputArgument;
use JuanchoSL\RequestListener\Enums\InputOption;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerAwareInterface;

interface UseCaseInterface extends LoggerAwareInterface
{

    /**
     * Enable or disable debug mode
     * @param bool $debug True for enable, false otherwisw
     * @return void
     */
    public function setDebug(bool $debug): static;

    /**
     * The command name in order to call from console
     * @return string The command name
     */
    //public function getName(): string;

    /**
     * Execute the command
     * @param ServerRequestInterface $arguments console parameters
     * @param ResponseInterface $response data output
     * @return int The execution result code
     */
    public function run(ServerRequestInterface $args, ResponseInterface $response, ?string $method = null): ResponseInterface;

    /**
     * Summary of addArgument
     * @param string $name
     * @param \JuanchoSL\RequestListener\Enums\InputArgument $required
     * @param \JuanchoSL\RequestListener\Enums\InputOption $option
     * @return void
     */
    public function addArgument(string $name, InputArgument $required, InputOption $option): void;

    /**
     * Retrieve the parameter value indicated with name
     * @param string $name The parameter name
     * @return mixed The parameter value
     */
    public function getArgument(string $name): mixed;
}