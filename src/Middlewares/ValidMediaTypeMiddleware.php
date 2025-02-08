<?php declare(strict_types=1);

namespace JuanchoSL\RequestListener\Middlewares;

use JuanchoSL\Exceptions\UnsupportedMediaTypeException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ValidMediaTypeMiddleware implements MiddlewareInterface
{

    protected array $mime_types = ['*/*', 'text/html', 'application/json', 'multipart/form-data'];

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        if ($request->hasHeader('accept')) {
            $accepts = $request->getHeaderLine('accept');
            foreach (explode(',', $accepts) as $accept) {
                if (($length = strpos($accept, ';')) !== false) {
                    $accept = substr($accept, 0, $length);
                }
                if (in_array($accept, $this->mime_types)) {
                    $content_type = $accept;
                    break;
                }
            }
            if (empty($content_type)) {
                throw new UnsupportedMediaTypeException("Any acepted media type ({$accepts}) are supported");
            }
            $response = $response->withAddedHeader('Content-type', $content_type);
        }
        return $response;
    }
}