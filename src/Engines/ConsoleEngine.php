<?php declare(strict_types=1);

namespace JuanchoSL\RequestListener\Engines;

use JuanchoSL\HttpData\Containers\ServerRequest;
use JuanchoSL\HttpData\Factories\StreamFactory;
use JuanchoSL\RequestListener\Contracts\EnginesInterface;
use JuanchoSL\RequestListener\Enums\OptionsEnum;
use Psr\Http\Message\ResponseInterface;

class ConsoleEngine implements EnginesInterface
{

    use EngineTrait;

    public static function parse(array $argv): static
    {
        $params = [];
        $key = null;
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

        $return = (new ServerRequest)
            ->withMethod(OptionsEnum::CLI->value)
            ->withQueryParams(static::sanitize($params))
            ->withRequestTarget($_SERVER['argv'][1] ?? '');

        $body = (new StreamFactory())->createStreamFromResource(STDIN);
        if ($body->getSize() > 0) {
            $return = $return->withBody($body);
            if (($mimetype = mime_content_type(STDIN)) !== false) {
                $return = $return->withAddedHeader('content-type', $mimetype);
            }
        }
        return new static($return);
    }

    public function sendMessage(ResponseInterface $response)
    {
        defined('STDOUT') or define('STDOUT', fopen('php://output', 'w+'));
        fwrite(STDOUT, (string) $response->getBody());
        //fclose(STDOUT);
        die($response->getStatusCode());
    }
}