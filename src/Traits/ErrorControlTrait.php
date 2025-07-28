<?php declare(strict_types=1);

namespace JuanchoSL\RequestListener\Traits;

use ErrorException;
use JuanchoSL\RequestListener\Contracts\ErrorHandlerInterface;

trait ErrorControlTrait
{

    protected ErrorHandlerInterface $error_handler;

    public function handlerError(int $errno, string $errstr, string $errfile, int $errline, array $context = []): bool
    {
        if (!(error_reporting() & $errno)) {
            return false;
        }
        $error = new ErrorException($errstr, $errno, $errno, $errfile, $errline);
        $this->handlerException($error, $context);
        return true;
    }

    public function handlerException(\Throwable $error, $context = [])
    {
        $this->stream->sendMessage(call_user_func_array($this->error_handler, [$this->stream->getRequest(), $error]));
    }

    public function setErrorHandler(ErrorHandlerInterface $error_handler): static
    {
        set_error_handler([$this, 'handlerError'], error_reporting());
        set_exception_handler([$this, 'handlerException']);
        $this->error_handler = $error_handler;
        return $this;
    }

}