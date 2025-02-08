<?php declare(strict_types=1);

namespace JuanchoSL\RequestListener\Handlers;

use JuanchoSL\HttpData\Factories\ResponseFactory;
use JuanchoSL\HttpData\Factories\StreamFactory;
use JuanchoSL\HttpHeaders\Constants\Types\MimeTypes;
use JuanchoSL\RequestListener\Contracts\ErrorHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class MyErrorHandler implements LoggerAwareInterface, ErrorHandlerInterface
{
    use LoggerAwareTrait;

    protected $display_error_details = false;
    protected $log_errors = false;
    protected $log_error_details = false;

    public function setDisplayErrorsDetails(bool $display_error_details): static
    {
        $this->display_error_details = $display_error_details;
        return $this;
    }
    public function setLogErrorsDetails(bool $log_error_details): static
    {
        $this->log_error_details = $log_error_details;
        return $this;
    }
    public function setLogErrors(bool $log_errors): static
    {
        $this->log_errors = $log_errors;
        return $this;
    }
    public function __invoke(
        ServerRequestInterface $input,
        \Throwable $exception
    ): ResponseInterface {
        $error = $exception->getMessage();
        $details = [
            'attributes' => $input->getAttributes(),
            'params' => $input->getQueryParams(),
            'body' => (string) $input->getBody()
        ];

        if ($this->display_error_details) {
            $error = [
                'error' => $error . " (" . $exception->getCode() . ") " . $exception->getFile() . ": " . $exception->getLine(),
                'context' => $details
            ];
        }
        if ($this->log_errors) {
            $context = [];
            if ($this->log_error_details) {
                $context = $details;
                $context['exception'] = $exception;
            }
            $this->logger?->error($exception->getMessage(), $context);
        }
        if (str_contains($input->getHeaderLine('accept'), MimeTypes::JSON)) {
            $error = json_encode($error, JSON_PRETTY_PRINT);
            $header = MimeTypes::JSON;
        } else {
            $error = print_r($error, true);
            $header = MimeTypes::PLAIN;
            if (str_contains($input->getHeaderLine('accept'), MimeTypes::HTML)) {
                $header = MimeTypes::HTML;
                $error = "<pre>" . $error . "</pre>";
            }
        }
        return (new ResponseFactory)->createResponse($exception->getCode())->withHeader('content-type', $header)->withBody((new StreamFactory)->createStream($error));
    }
}