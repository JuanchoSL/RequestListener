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
        return new static((new ServerRequestFactory)->fromGlobals()->withRequestTarget($_SERVER['SCRIPT_URL']));
    }

    public function sendMessage(ResponseInterface $response)
    {
        http_response_code($response->getStatusCode());
        if (!empty($response)) {
            foreach ($response->getHeaders() as $name => $value) {
                header("{$name}: " . $response->getHeaderLine($name));
            }
        }
        echo (string) $response->getBody();
        return max(200, $response->getStatusCode());
    }
}