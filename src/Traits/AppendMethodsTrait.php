<?php declare(strict_types=1);

namespace JuanchoSL\RequestListener\Traits;

use JuanchoSL\RequestListener\Contracts\MiddlewareableInterface;
use JuanchoSL\RequestListener\Contracts\UseCaseInterface;
use JuanchoSL\RequestListener\Entities\Router;
use JuanchoSL\RequestListener\Entities\RouterGroup;
use JuanchoSL\RequestListener\Enums\OptionsEnum;

trait AppendMethodsTrait
{

    protected ?RouterGroup $group = null;
    protected string $basePattern = '';

    public function cli(string $alias, UseCaseInterface|callable|string|array $command): MiddlewareableInterface
    {
        return $this->add($alias, $command, OptionsEnum::CLI);
    }

    public function get(string $alias, UseCaseInterface|callable|string|array $command): MiddlewareableInterface
    {
        return $this->add($alias, $command, OptionsEnum::GET);
    }

    public function post(string $alias, UseCaseInterface|callable|string|array $command): MiddlewareableInterface
    {
        return $this->add($alias, $command, OptionsEnum::POST);
    }

    public function put(string $alias, UseCaseInterface|callable|string|array $command): MiddlewareableInterface
    {
        return $this->add($alias, $command, OptionsEnum::PUT);
    }

    public function patch(string $alias, UseCaseInterface|callable|string|array $command): MiddlewareableInterface
    {
        return $this->add($alias, $command, OptionsEnum::PATCH);
    }
    public function delete(string $alias, UseCaseInterface|callable|string|array $command): MiddlewareableInterface
    {
        return $this->add($alias, $command, OptionsEnum::DELETE);
    }
    public function connect(string $alias, UseCaseInterface|callable|string|array $command): MiddlewareableInterface
    {
        return $this->add($alias, $command, OptionsEnum::CONNECT);
    }
    public function crud(string $alias, UseCaseInterface|callable|string|array $command): MiddlewareableInterface
    {
        $app = $this;
        return $this->group($alias, function () use ($app, $command) {
            foreach (['select' => OptionsEnum::GET, 'insert' => OptionsEnum::POST, 'overwrite' => OptionsEnum::PUT, 'update' => OptionsEnum::PATCH, 'delete' => OptionsEnum::DELETE] as $function => $method) {
                $app->add('', [$command, $function], $method);
            }
        });
    }

    protected function add(string $alias, UseCaseInterface|callable|string|array $command, OptionsEnum ...$valid_options): ?MiddlewareableInterface
    {
        foreach ($valid_options as $option) {
            $route = new Router(strtoupper($option->value), $this->basePattern . $alias, $command);
            $route = $this->main_handler->add($route);
            if (!empty($this->group)) {
                $this->group->add($route);
            }
            return $route;
        }
        return null;
    }

    public function group($pattern, \Closure $callable): MiddlewareableInterface
    {
        $oldBasePattern = $this->basePattern;
        $group = $this->group;
        $this->basePattern .= $pattern;
        $this->group = $new_group = new RouterGroup();
        if (is_callable($callable)) {
            call_user_func($callable);
        }
        $this->group = $group;
        if (!empty($this->group)) {
            $this->group->add($new_group);
        }
        $this->basePattern = $oldBasePattern;
        return $new_group;
    }
}