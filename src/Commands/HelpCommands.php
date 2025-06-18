<?php declare(strict_types=1);

namespace JuanchoSL\RequestListener\Commands;

use JuanchoSL\DataTransfer\Factories\DataConverterFactory;
use JuanchoSL\DataTransfer\Factories\DataTransferFactory;
use JuanchoSL\DataTransfer\Repositories\ArrayDataTransfer;
use JuanchoSL\HttpData\Factories\StreamFactory;
use JuanchoSL\HttpHeaders\Constants\Types\MimeTypes;
use JuanchoSL\RequestListener\Enums\OptionsEnum;
use JuanchoSL\RequestListener\UseCases;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HelpCommands extends UseCases
{
    protected $commands = [];

    public function setCommands($commands)
    {
        $this->commands = $commands;
        return $this;
    }
    protected function configure(): void
    {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $commands = [];
        foreach ($this->commands as $name => $methods) {
            $commands[] = [
                'target' => $name,
                'methods' => implode('|', array_keys($methods))
            ];
        }
        if ($request->hasHeader('accept')) {
            $mime_type = $request->getHeader('accept');
            try {
                $body = DataConverterFactory::asMimeType(DataTransferFactory::create($commands), $mime_type);
            } catch (\Exception $e) {

            }
        }
        if ($request->getMethod() == OptionsEnum::CLI->value || PHP_SAPI == 'cli') {
            $mime_type = MimeTypes::PLAIN;
            $body = $this->toText($commands);
        } else {
            $mime_type = MimeTypes::HTML;
            $body = $this->toHtml($commands);
        }
        return $response
            ->withBody((new StreamFactory)->createStream((string) $body))
            ->withHeader('content-type', $mime_type);
    }

    protected function toText($commands)
    {
        $body[] = "Available commands:";
        foreach ($commands as $line => $data) {
            $body[] = "\t- {$data['target']} [{$data['methods']}]";
        }
        return implode("\r\n", $body);
    }

    protected function toHtml($commands)
    {
        $body = "";
        foreach ($commands as $values) {
            $body .= sprintf("<li>%s [%s]</li>", $values['target'], $values['methods']);
        }
        return "<p>Available commands</p>" . "<ul>" . $body . "</ul>";
    }
}