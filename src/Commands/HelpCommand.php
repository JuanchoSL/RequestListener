<?php declare(strict_types=1);

namespace JuanchoSL\RequestListener\Commands;

use JuanchoSL\DataTransfer\Factories\DataConverterFactory;
use JuanchoSL\DataTransfer\Factories\DataTransferFactory;
use JuanchoSL\HttpData\Factories\StreamFactory;
use JuanchoSL\HttpHeaders\Constants\Types\MimeTypes;
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
        $commands = [];
        foreach ($request->getAttribute('arguments') as $name => $methods) {
            $commands[] = [
                'name' => $name,
                'argument' => $methods['argument']->value,
                'option' => $methods['option']->value
            ];
        }
        if ($request->hasHeader('accept')) {
            $mime_type = $request->getHeader('accept');
            try {
                $body = DataConverterFactory::asMimeType(DataTransferFactory::create($commands), $mime_type);
            } catch (\Exception $e) {

            }
        }
        if (empty($body)) {
            if ($request->getMethod() == OptionsEnum::CLI->value || PHP_SAPI == 'cli') {
                $mime_type = MimeTypes::PLAIN;
                $body = $this->toText($commands, $request->getRequestTarget());
            } else {
                $mime_type = MimeTypes::HTML;
                $body = $this->toHtml($commands, $request->getRequestTarget());
            }
        }
        return $response
            ->withBody((new StreamFactory)->createStream((string) $body))
            ->withHeader('content-type', $mime_type);
    }

    protected function toText($request, $target)
    {
        $body[] = sprintf("Available arguments for command %s:", $target);
        foreach ($request as $name => $values) {
            $body[] = sprintf("\t- %s: %s, %s", $values['name'], $values['argument'], $values['option']);
        }
        return implode("\r\n", $body);
    }

    protected function toHtml($request, $target)
    {
        $body = "";
        foreach ($request as $name => $values) {
            $body .= sprintf("<li>%s: %s, %s</li>", $values['name'], $values['argument'], $values['option']);
        }
        return "<p>" . sprintf("Available arguments for command %s:", $target) . "</p>" . "<ul>" . $body . "</ul>";
    }
}