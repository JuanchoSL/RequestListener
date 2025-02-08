<?php declare(strict_types=1);

namespace JuanchoSL\RequestListener\Engines;

use Psr\Http\Message\ServerRequestInterface;

trait EngineTrait
{

    protected ServerRequestInterface $request;

    public function __construct(ServerRequestInterface $request)
    {
        $this->request = $request;
    }
    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }

    public static function sanitize($value)
    {
        if (is_iterable($value)) {
            foreach ($value as $key => $val) {
                $value[$key] = static::sanitize($val);
            }
        } else {
            return filter_var($value, FILTER_SANITIZE_ADD_SLASHES);
        }
        return $value;
    }

}