<?php

declare(strict_types=1);

namespace JuanchoSL\RequestListener\Tests;

use JuanchoSL\RequestListener\Enums\InputArgument;
use JuanchoSL\RequestListener\Enums\InputOption;
use JuanchoSL\RequestListener\UseCases;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class UseCaseCommands extends UseCases
{

    protected function configure(): void
    {
        $this->addArgument('required_single', InputArgument::REQUIRED, InputOption::SINGLE);
        $this->addArgument('required_multi', InputArgument::REQUIRED, InputOption::MULTI);
        $this->addArgument('optional_void', InputArgument::OPTIONAL, InputOption::VOID);
        $this->addArgument('optional_single_int', InputArgument::OPTIONAL, InputOption::SINGLE_INT);
        $this->addArgument('optional_multi_int', InputArgument::OPTIONAL, InputOption::MULTI_INT);
    }

    public function execute(ServerRequestInterface $input, ResponseInterface $response): ResponseInterface
    {
        print_r($input);exit;
        print_r($input->getParsedBody());exit;
        $body = $response->getBody();
        $body->write('execute');
        $response = $response->withBody($body);
        return $this->__invoke($input, $response);
    }
    public function __invoke(ServerRequestInterface $input, ResponseInterface $response): ResponseInterface
    {
        $body = $response->getBody();
        $body->write(json_encode($input->getAttributes(), JSON_PRETTY_PRINT));
        //$body->write("<pre>" . print_r($input, true) . "</pre>");
        return $response->withBody($body)->withAddedHeader('content-type','application/json');
    }
    public function files(ServerRequestInterface $input, ResponseInterface $response): ResponseInterface
    {
        print_r($input);exit;
        $body = $input->getBody();
        print_r((string)$body);exit;
        print_r(request_parse_body());exit;
        $body->write("invoke\r\n");
        $body->write("<pre>" . print_r($input, true) . "</pre>");
        return $response->withBody($body);
    }
}