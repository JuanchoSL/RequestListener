<?php declare(strict_types=1);

namespace JuanchoSL\RequestListener\Entities;

use JuanchoSL\DataTransfer\Contracts\DataTransferInterface;
use JuanchoSL\Exceptions\NotFoundException;
use Psr\Container\ContainerInterface;

class InputImmutable implements ContainerInterface, \JsonSerializable
{

    protected array $arguments = [];

    public function __construct(array $data)
    {
        $this->arguments = $data;
    }

    public function get($name): mixed
    {
        if (array_key_exists($name, $this->arguments)) {
            return $this->arguments[$name];
        }
        throw new NotFoundException("The element {$name} does not exists into Container");
    }

    public function has($name): bool
    {
        return (array_key_exists($name, $this->arguments));
        return $this->arguments->has($name);
    }


    public function jsonSerialize(): array
    {
        return $this->arguments;
    }
}
