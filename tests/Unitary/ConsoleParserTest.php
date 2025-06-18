<?php

namespace JuanchoSL\RequestListener\Tests\Unitary;

use JuanchoSL\RequestListener\Contracts\EnginesInterface;
use JuanchoSL\RequestListener\Engines\ConsoleEngine;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

class ConsoleParserTest extends TestCase
{

    public function testConsoleGetParser()
    {
        $_SERVER['argv'] = [];
        $_SERVER['argv'][] = 'entrypoint';
        $_SERVER['argv'][] = 'usecase';
        $_SERVER['argv'][] = '--required_void';
        $_SERVER['argv'][] = '--required_multi';
        $_SERVER['argv'][] = 'a';
        $_SERVER['argv'][] = 'b';
        $_SERVER['argv'][] = 'c';
        $engine = ConsoleEngine::parse();
        $this->assertInstanceOf(EnginesInterface::class, $engine);
        $request = $engine->getRequest();
        $this->assertInstanceOf(ServerRequestInterface::class, $request);
        $attributes = $request->getQueryParams();
        $this->assertIsArray($attributes);
        $this->assertArrayHasKey('required_multi', $attributes);
        $multi = $attributes['required_multi'];
        $this->assertIsArray($multi);
        $this->assertContains('a', $multi);
        $this->assertContains('b', $multi);
        $this->assertContains('c', $multi);
    }

    public function testConsoleUriParser()
    {
        $_SERVER['argv'] = [];
        $_SERVER['argv'][] = 'entrypoint';
        $_SERVER['argv'][] = 'usecase';
        $_SERVER['argv'][] = '--required_void';
        $_SERVER['argv'][] = '--required_multi';
        $_SERVER['argv'][] = 'a';
        $_SERVER['argv'][] = 'b';
        $_SERVER['argv'][] = 'c';
        $engine = ConsoleEngine::parse();
        $this->assertInstanceOf(EnginesInterface::class, $engine);
        $request = $engine->getRequest();
        $this->assertInstanceOf(ServerRequestInterface::class, $request);
        $this->assertEquals(sprintf('http://%s/usecase?%s', gethostname(), http_build_query(['required_void' => '1', 'required_multi' => ['a', 'b', 'c']])), (string) $request->getUri());
        $attributes = $request->getQueryParams();
        $this->assertIsArray($attributes);
        $this->assertArrayHasKey('required_multi', $attributes);
        $multi = $attributes['required_multi'];
        $this->assertIsArray($multi);
        $this->assertContains('a', $multi);
        $this->assertContains('b', $multi);
        $this->assertContains('c', $multi);
    }
}