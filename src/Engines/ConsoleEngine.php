<?php declare(strict_types=1);

namespace JuanchoSL\RequestListener\Engines;

use JuanchoSL\HttpData\Factories\RequestFactory;
use JuanchoSL\HttpData\Factories\ServerRequestFactory;
use JuanchoSL\HttpData\Factories\StreamFactory;
use JuanchoSL\RequestListener\Contracts\EnginesInterface;
use JuanchoSL\RequestListener\Enums\OptionsEnum;
use Psr\Http\Message\ResponseInterface;

class ConsoleEngine implements EnginesInterface
{

    use EngineTrait;

    public static function parse(): static
    {
        $params = [];
        $key = null;
        $argv = $_SERVER['argv'];
        foreach ($argv as $argument) {
            $value = true;
            if (substr($argument, 0, 2) == '--') {
                $argument = substr($argument, 2);

                if (strpos($argument, '=') !== false) {
                    list($argument, $value) = explode('=', $argument);
                }
                $key = $argument;
            } elseif (!is_null($key)) {
                $value = $argument;
            }
            if (isset($key)) {
                if (!array_key_exists($key, $params) or ($params[$key] === true)) {
                    $params[$key] = $value;
                } else {
                    if (!is_array($params[$key])) {
                        $params[$key] = ($params[$key] !== true) ? [$params[$key]] : [];
                    }
                    $params[$key][] = $value;
                }
            }
        }
        $return = (new RequestFactory)
            ->createRequest(OptionsEnum::GET->value, 'http://' . str_replace("//", '/', gethostname() . "/" . $_SERVER['argv'][1]) . "?" . http_build_query($params));

        defined('STDIN') or define('STDIN', fopen('php://input', 'a+'));
        $body = (new StreamFactory())->createStreamFromResource(STDIN);
        if ($body->getSize() > 0) {
            $return = $return->withBody($body)->withMethod(OptionsEnum::POST->value);
            if (($mimetype = mime_content_type(STDIN)) !== false) {
                $return = $return->withAddedHeader('content-type', $mimetype);
            }
        }
        $return = (new ServerRequestFactory)->fromRequest($return)->withRequestTarget($_SERVER['argv'][1]);
        return new static($return);
    }

    public function sendMessage(ResponseInterface $response): int
    {
        $limit = 4000;
        $body = (empty($response->getBody()->getSize())) ? $response->getStatusCode() . " " . $response->getReasonPhrase() : (string) $response->getBody();
        $body .= PHP_EOL;
        if ($response->getStatusCode() < $limit) {
            defined('STDOUT') or define('STDOUT', fopen('php://output', 'a+'));
            fwrite(STDOUT, $body);
        } else {
            defined('STDERR') or define('STDERR', fopen('php://stdout', 'a+'));
            fwrite(STDERR, $body);
        }
        return $response->getStatusCode();
    }
}