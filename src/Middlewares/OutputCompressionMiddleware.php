<?php declare(strict_types=1);

namespace JuanchoSL\RequestListener\Middlewares;

use JuanchoSL\HttpData\Factories\StreamFactory;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;

class OutputCompressionMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $compressed = null;
        $response = $handler->handle($request);

        if ($request->hasHeader('Accept-Encoding') && !$response->hasHeader('Content-Encoding') && $response->getBody()->getSize() > 0) {
            $accepts = $request->getHeader('Accept-Encoding');
            foreach ($accepts as $accept) {
                $accept = trim($accept);
                switch ($accept) {
                    case 'br':
                        if (function_exists('brotli_compress')) {
                            $compressed = brotli_compress((string) $response->getBody());
                        }
                        break;

                    case 'deflate':
                        if (function_exists('gzcompress')) {
                            $compressed = gzcompress((string) $response->getBody());
                        }
                        break;

                    case 'gzip':
                        if (function_exists('gzencode')) {
                            $compressed = gzencode((string) $response->getBody());
                        }
                        break;

                    case 'zstd':
                        if (function_exists('zstd_compress')) {
                            $compressed = zstd_compress((string) $response->getBody());
                        }
                        break;
                }
                if (!empty($compressed)) {
                    $compressed = (new StreamFactory)->createStream($compressed);
                    return $response
                        ->withHeader('Vary', 'Accept-Encoding')
                        ->withHeader('Content-Encoding', $accept)
                        ->withHeader('Content-Length', (string) $compressed->getSize())
                        ->withBody($compressed);
                }
            }
        }
        return $response;
    }
}