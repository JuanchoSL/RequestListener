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
        /*
        if ($request->getMethod() == OptionsEnum::CLI->value) {
            $body = $this->toText($request);
            $response = $response->withHeader('Content-type', 'text/plain');
        } else {
            $body = $this->toHtml($request);
            $response = $response->withHeader('Content-type', 'text/html');
        }*/
        return $response->withBody((new StreamFactory)->createStream($body));

        $body[] = "Available commands:";
        foreach ($request->getAttribute('commands') as $line => $data) {
            $body[] = "\t- {$line}";
        }
        $body = implode("\r\n", $body);
        if ($request->getMethod() != OptionsEnum::CLI->value) {
            $body = "<pre>" . nl2br($body) . "</pre>";
        }
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