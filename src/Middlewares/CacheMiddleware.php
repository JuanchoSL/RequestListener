<?php declare(strict_types=1);

namespace JuanchoSL\RequestListener\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\SimpleCache\CacheInterface;

class CacheMiddleware implements MiddlewareInterface
{
    protected CacheInterface $cache;

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $now = gmdate("D, d M Y H:i:s", time());
        if (
            $request->hasHeader('cache-control') && str_starts_with($request->getHeaderLine('cache-control'), 'no-cache')
            ||
            $request->hasHeader('pragma') && str_starts_with($request->getHeaderLine('pragma'), 'no-cache')
        ) {
            $response = $handler->handle($request)->withHeader('last-modified', $now . " GMT")->withHeader('pragma', 'no-cache')->withHeader('cache-control', 'no-cache');
        } else {

        }
        $cache_key = md5((string) $request->getUri());
        if (!is_null($this->cache)) {
            $obj = $this->cache->get($cache_key);
            $time = $obj->cache_last;
        } else {
            $time = $now;
        }
        $etag = md5($cache_key . $time);
        if (
            ($request->hasHeader('If-modified-since') && $time <= $request->getHeaderLine('if-modified-since'))
            &&
            ($request->hasHeader('If-none-match') && $etag == trim($request->getHeaderLine('If-none-match'), '"'))
        ) {
            return $response->withStatus(304);
        }else{

        }
        $response = $handler->handle($request);
        return $response;
    }
}