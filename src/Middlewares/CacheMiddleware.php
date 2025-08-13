<?php declare(strict_types=1);

namespace JuanchoSL\RequestListener\Middlewares;

use JuanchoSL\HttpData\Factories\ResponseFactory;
use JuanchoSL\HttpData\Factories\StreamFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\SimpleCache\CacheInterface;

class CacheMiddleware implements MiddlewareInterface
{

    protected ?CacheInterface $cache = null;
    protected int $ttl;

    public function __construct(?CacheInterface $cache = null, int $ttl = 3600)
    {
        $this->cache = $cache;
        $this->ttl = $ttl;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        //        $now = gmdate("D, d M Y H:i:s", time());

        if (
                $request->hasHeader('cache-control') && str_starts_with($request->getHeaderLine('cache-control'), 'no-cache') ||
                $request->hasHeader('pragma') && str_starts_with($request->getHeaderLine('pragma'), 'no-cache')
        ) {
            return $handler->handle($request)
                            ->withAddedHeader("Expires", gmdate("D, d M Y H:i:s", 1) . " GMT")
                            ->withAddedHeader("Last-Modified", gmdate("D, d M Y H:i:s", time()) . " GMT")
                            ->withAddedHeader("Cache-Control", ["no-store", "no-cache", "must-revalidate"])
                            ->withAddedHeader("Pragma", "no-cache")
            ;
        } else {

            $cache_key = md5((string) $request->getUri()) . '-' . $request->getUri()->getHost() . '.' . pathinfo($request->getRequestTarget(), PATHINFO_EXTENSION) ?? 'cache';
            if (!is_null($this->cache)) {
                $obj = $this->cache->get($cache_key);
            }
            $cache_last = (empty($obj)) ? time() : $obj['cache_last'];
            $etag = md5($cache_key . $cache_last);

            if (!empty($obj)) {

                if (
                        ($request->hasHeader('If-modified-since') && $cache_last <= strtotime($request->getHeaderLine('if-modified-since'))) ||
                        ($request->hasHeader('If-none-match') && $etag == trim($request->getHeaderLine('If-none-match'), '"'))
                ) {
                    return (new ResponseFactory)->createResponse(\Fig\Http\Message\StatusCodeInterface::STATUS_NOT_MODIFIED);
                }

                $response = (new ResponseFactory)->createResponse()->withHeader('content-type', $obj['mime_type']);
                $data = $obj['data'];
                $age = ($cache_last + $this->ttl) - time();
            } else {
                $response = $handler->handle($request);
                $data = (string) $response->getBody();
                if (!empty($this->cache)) {
                    $this->cache->set(
                            $cache_key,
                            [
                                'mime_type' => $response->getHeaderLine('content-type'),
                                'cache_last' => $cache_last,
                                'data' => $data
                            ],
                            $this->ttl
                    );
                }
                $age = $this->ttl;
            }
            $cache_control = ($request->hasHeader('Authorization')) ? "private" : 'public';
            return $response
                            ->withBody((new StreamFactory)->createStream($data))
                            ->withAddedHeader("Expires", gmdate("D, d M Y H:i:s", $cache_last + $this->ttl) . " GMT")
                            ->withAddedHeader("Last-Modified", gmdate("D, d M Y H:i:s", $cache_last) . " GMT")
                            ->withAddedHeader("Cache-Control", ["max-age={$age}", $cache_control, "must-revalidate", "min-fresh=" . ceil($age / 2)])
                            ->withAddedHeader("User-Cache-Control", "max-age={$age}")
                            ->withAddedHeader("Pragma", ["cache", "must-revalidate"])
                            ->withAddedHeader("ETag", '"' . $etag . '"')
            ;
        }
    }
}
