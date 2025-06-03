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
        $uri = array_key_exists('HTTPS', $_SERVER) && strtoupper($_SERVER['HTTPS']) == 'ON' ? 'https' : 'http';
        $uri .= '://';
        foreach (['HTTP_HOST', 'SERVER_NAME', 'HOSTNAME'] as $target) {
            if (array_key_exists($target, $_SERVER)) {
                $uri .= $_SERVER[$target];
                break;
            }
        }
        $uri .= $_SERVER['REQUEST_URI'];
        foreach (['SCRIPT_URL', 'PATH_INFO', 'REQUEST_URI'] as $target) {
            if (array_key_exists($target, $_SERVER)) {
                $target = $_SERVER[$target];
                break;
            }
        }

        if (empty($_GET) && !empty($_SERVER['QUERY_STRING'])) {
            mb_parse_str($_SERVER['QUERY_STRING'], $get);
        } else {
            $get = $_GET;
        }
        return new static((new ServerRequestFactory)
            ->createServerRequest($_SERVER['REQUEST_METHOD'], $uri)
            ->withQueryParams(static::sanitize($get))
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
        return max(200, $response->getStatusCode());
    }
}