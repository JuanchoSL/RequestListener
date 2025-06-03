<?php declare(strict_types=1);

namespace JuanchoSL\RequestListener\Commands;

use JuanchoSL\HttpData\Factories\StreamFactory;
use JuanchoSL\RequestListener\Enums\OptionsEnum;
use JuanchoSL\RequestListener\UseCases;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HelpCommands extends UseCases
{
    protected function configure(): void
    {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {

        $body = ($request->getMethod() == OptionsEnum::CLI->value) ? $this->toText($request) : $this->toHtml($request);
        return $response->withBody((new StreamFactory)->createStream($body));
    }

    protected function toText($request)
    {
        $body[] = "Available commands:";
        foreach ($request->getAttribute('commands') as $line => $data) {
            $body[] = "\t- {$line}";
        }
        return implode("\r\n", $body);
    }

    protected function toHtml($request)
    {
        $body = "";
        foreach ($request->getAttribute('commands') as $name => $values) {
            $body .= sprintf("<li>%s</li>", $name);
        }
        return "<p>Available commands</p>" . "<ul>" . $body . "</ul>";
    }
}