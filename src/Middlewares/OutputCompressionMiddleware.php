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
        $compress = false;
        $encoding = false;
        if ($request->hasHeader('Accept-Encoding')) {
            $accepts = $request->getHeader('Accept-Encoding');
            if (is_string(current($accepts))) {
                $accepts = explode(',', current($accepts));
            }
            foreach ($accepts as $accept) {
                $accept = trim($accept);
                switch ($accept) {
                    case 'deflate':
                        $encoding = 'deflate';
                        $compress = ZLIB_ENCODING_DEFLATE;
                        break;

                    case 'gzip':
                        $encoding = 'gzip';
                        $compress = ZLIB_ENCODING_GZIP;
                        break;
                }
                if ($encoding !== false) {
                    break;
                }
            }
        }
        $response = $handler->handle($request);
        if (!$compress || $response->hasHeader('Content-Encoding')) {
            // Browser doesn't accept compression
            return $response;
        }
        // Compress response data
        $deflateContext = deflate_init($compress, ['level' => 9]);
        $compressed = deflate_add($deflateContext, (string) $response->getBody(), \ZLIB_FINISH);
/*
        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $compressed);
        rewind($stream);
*/
        $compressed = (new StreamFactory)->createStream($compressed);
        return $response
            ->withHeader('Content-Encoding', $encoding)
            ->withHeader('Content-Length', (string) $compressed->getSize())
            ->withBody($compressed);
    }
}