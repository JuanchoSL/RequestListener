<?php

namespace JuanchoSL\RequestListener\Tests\Unitary;

use JuanchoSL\RequestListener\Contracts\EnginesInterface;
use JuanchoSL\RequestListener\Engines\WebEngine;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

class WebParserTest extends TestCase
{

    public function testWebGetParserRequesrtUri()
    {

        $_SERVER['HTTPS'] = 'OFF';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['SCRIPT_URL'] = '/usercase';
        $_SERVER['QUERY_STRING'] = http_build_query([
            "required_void" => 1,
            "required_multi" => ['a', 'b', 'c']
        ]);
        $_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_URL'] ."?" . $_SERVER['QUERY_STRING'];
        
        $engine = WebEngine::parse();
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
    public function testWebGetParserScriptUrl()
    {

        $_SERVER['HTTPS'] = 'OFF';
        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['SCRIPT_URL'] = '/usercase';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['QUERY_STRING'] = http_build_query([
            "required_void" => 1,
            "required_multi" => ['a', 'b', 'c']
        ]);
        $engine = WebEngine::parse();
        $this->assertInstanceOf(EnginesInterface::class, $engine);
        $request = $engine->getRequest();
        $this->assertInstanceOf(ServerRequestInterface::class, $request);
        $this->assertEquals(sprintf('http://%s/usercase?%s', 'localhost', http_build_query(['required_void' => '1', 'required_multi' => ['a', 'b', 'c']])), (string) $request->getUri());
        $attributes = $request->getQueryParams();
        $this->assertIsArray($attributes);
        $this->assertArrayHasKey('required_multi', $attributes);
        $multi = $attributes['required_multi'];
        $this->assertIsArray($multi);
        $this->assertContains('a', $multi);
        $this->assertContains('b', $multi);
        $this->assertContains('c', $multi);
    }

    public function testWebUriParser()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['HTTPS'] = 'OFF';
        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['SCRIPT_URL'] = '/usercase';
        $_SERVER['QUERY_STRING'] = http_build_query([
            "required_void" => 1,
            "required_multi" => ['a', 'b', 'c']
        ]);
        $engine = WebEngine::parse();
        $this->assertInstanceOf(EnginesInterface::class, $engine);
        $request = $engine->getRequest();
        $this->assertInstanceOf(ServerRequestInterface::class, $request);
        $this->assertEquals(sprintf('http://%s/usercase?%s', 'localhost', http_build_query(['required_void' => '1', 'required_multi' => ['a', 'b', 'c']])), (string) $request->getUri());
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