<?php declare(strict_types=1);

namespace JuanchoSL\RequestListener\Commands;

use JuanchoSL\HttpData\Factories\StreamFactory;
use JuanchoSL\RequestListener\Enums\OptionsEnum;
use JuanchoSL\RequestListener\UseCases;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HelpCommand extends UseCases
{
    protected function configure(): void
    {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $body = ($request->getMethod() == OptionsEnum::CLI->value) ? $this->toText($request) : $this->toHtml($request);
        return $response->withBody((new StreamFactory)->createStream($body));
    }
    /*
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $body[] = sprintf("Available arguments for command %s:", $request->getRequestTarget());
        foreach ($request->getAttribute('arguments') as $name => $values) {
            $body[] = sprintf("\t- %s: %s, %s", $name, $values['argument']->value, $values['option']->value);
            }
        $body[] = sprintf("Current working dir: %s", getcwd());
        $body = implode("\r\n", $body);
        if ($request->getMethod() != OptionsEnum::CLI->value) {
            $body = "<pre>" . nl2br($body) . "</pre>";
            }
            return $response->withBody((new StreamFactory)->createStream($body));
            }
            */
    protected function toText($request)
    {
        $body[] = sprintf("Available arguments for command %s:", $request->getRequestTarget());
        foreach ($request->getAttribute('arguments') as $name => $values) {
            $body[] = sprintf("\t- %s: %s, %s", $name, $values['argument']->value, $values['option']->value);
        }
        return implode("\r\n", $body);
    }

    protected function toHtml($request)
    {
        $body = "";
        foreach ($request->getAttribute('arguments') as $name => $values) {
            $body .= sprintf("<li>%s: %s, %s</li>", $name, $values['argument']->value, $values['option']->value);
        }
        return "<p>" . sprintf("Available arguments for command %s:", $request->getRequestTarget()) . "</p>" . "<ul>" . $body . "</ul>";
    }
}