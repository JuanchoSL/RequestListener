<?php declare(strict_types=1);

namespace JuanchoSL\RequestListener\Middlewares;

use Fig\Http\Message\StatusCodeInterface;
use JuanchoSL\HttpData\Factories\ResponseFactory;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;

class RateLimitMiddleware implements MiddlewareInterface
{
    protected int $num_to_ban;
    protected int $seconds_to_ban;

    public function __construct(int $tries_to_ban = 5, int $seconds_to_ban = 360)
    {
        $this->num_to_ban = $tries_to_ban;
        $this->seconds_to_ban = $seconds_to_ban;
    }
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start([
                'cookie_lifetime' => getenv('SESSION_TTL'),
            ]);
        }
        if (empty($_SESSION['tries_to_ban'])) {
            $_SESSION['tries_to_ban'] = 0;
        } elseif ($_SESSION['tries_to_ban'] >= $this->num_to_ban) {
            if (time() > ($_SESSION['tries_to_ban'] + $this->seconds_to_ban)) {
                $_SESSION['tries_to_ban'] = 0;
            } else {
                //throw new RateLimitedException("Your has been reached the rate limit");
                return (new ResponseFactory())->createResponse(StatusCodeInterface::STATUS_TOO_MANY_REQUESTS);
            }
        }

        $response = $handler->handle($request);

        if ($response->getStatusCode() == StatusCodeInterface::STATUS_UNAUTHORIZED) {
            $_SESSION['tries_to_ban'] += 1;
            if ($_SESSION['tries_to_ban'] == $this->num_to_ban) {
                $_SESSION['tries_to_ban'] = time() + $this->seconds_to_ban;
            }
        }

        return $response;
    }
}