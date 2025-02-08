<?php declare(strict_types=1);

namespace JuanchoSL\RequestListener\Entities;

use JuanchoSL\DataTransfer\Contracts\DataTransferInterface;
use JuanchoSL\Exceptions\NotFoundException;
use Psr\Container\ContainerInterface;

class InputImmutable implements ContainerInterface, \JsonSerializable
{

    protected DataTransferInterface $arguments;

    public function __construct(DataTransferInterface $data)
    {
        $this->arguments = $data;
    }

    public function get($name): mixed
    {
        if ($this->arguments->has($name)) {
            return $this->arguments->get($name);
        }
        throw new NotFoundException("The element {$name} does not exists into Container");
    }

    public function has($name): bool
    {
        return $this->arguments->has($name);
    }

    
    public function jsonSerialize(): array
    {
        return $this->arguments->jsonSerialize();
    }
}
