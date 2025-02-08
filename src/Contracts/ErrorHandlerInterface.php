<?php declare(strict_types=1);

namespace JuanchoSL\RequestListener\Contracts;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface ErrorHandlerInterface
{
    public function setDisplayErrorsDetails(bool $display_error_details): static;
    public function setLogErrorsDetails(bool $log_error_details): static;
    public function setLogErrors(bool $log_errors): static;
    public function __invoke(ServerRequestInterface $request, \Throwable $exception): ResponseInterface;
}