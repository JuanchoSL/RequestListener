<?php declare(strict_types=1);

namespace JuanchoSL\RequestListener\Engines;

use JuanchoSL\HttpData\Factories\ServerRequestFactory;
use JuanchoSL\RequestListener\Contracts\EnginesInterface;
use Psr\Http\Message\ResponseInterface;

class WebEngine implements EnginesInterface
{

    use EngineTrait;

    public static function parse(): static
    {
        $_SERVER['SCRIPT_URL'] ??= trim(str_replace($_SERVER['QUERY_STRING'] ?? '', '', $_SERVER['REQUEST_URI']), '?');
        return new static((new ServerRequestFactory)->fromGlobals()->withRequestTarget($_SERVER['SCRIPT_URL']));
    }

    public function sendMessage(ResponseInterface $response): int
    {
        http_response_code($response->getStatusCode());
        if (!empty($response)) {
            foreach ($response->getHeaders() as $name => $value) {
                header("{$name}: " . $response->getHeaderLine($name));
            }
        }
        if ($response->getBody()->getSize() > 0) {
            echo (string) $response->getBody();
        }

        return $response->getStatusCode();
    }
}