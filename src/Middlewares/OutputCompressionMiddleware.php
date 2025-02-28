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
                    case 'deflate':
                        $compressed = gzcompress((string) $response->getBody());
                        break;

                    case 'gzip':
                        $compressed = gzencode((string) $response->getBody());
                        break;
                }
                if (!empty($compressed)) {
                    $compressed = (new StreamFactory)->createStream($compressed);
                    return $response
                        ->withAddedHeader('Content-Encoding', $accept)
                        ->withAddedHeader('Content-Length', (string) $compressed->getSize())
                        ->withBody($compressed);
                }
            }
        }
        return $response;
    }
}