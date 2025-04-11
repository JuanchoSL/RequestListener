<?php declare(strict_types=1);

namespace JuanchoSL\RequestListener\Engines;

use JuanchoSL\HttpData\Factories\ServerRequestFactory;
use JuanchoSL\RequestListener\Contracts\EnginesInterface;
use Psr\Http\Message\ResponseInterface;

class WebEngine implements EnginesInterface
{

    use EngineTrait;

    public static function parse(array $parameters): static
    {
        $uri = array_key_exists('HTTPS', $_SERVER) && strtoupper($_SERVER['HTTPS']) == 'ON' ? 'https' : 'http';
        $uri .= '://';
        $uri .= $_SERVER['HTTP_HOST'];
        $uri .= $_SERVER['REQUEST_URI'];
        foreach (['SCRIPT_URL', 'PATH_INFO', 'REQUEST_URI'] as $target) {
            if (array_key_exists($target, $_SERVER)) {
                $target = $_SERVER[$target];
                break;
            }
        }
        return new static((new ServerRequestFactory)
            ->createServerRequest($_SERVER['REQUEST_METHOD'], $uri)
            ->withQueryParams(static::sanitize($_GET))
            ->withRequestTarget($target ?? ''));//SCRIPT_URL || PATH_INFO
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
        exit;
    }
}