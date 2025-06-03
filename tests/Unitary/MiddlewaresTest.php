<?php

namespace JuanchoSL\RequestListener\Tests\Unitary;

use Fig\Http\Message\RequestMethodInterface;
use JuanchoSL\HttpData\Factories\ServerRequestFactory;
use JuanchoSL\HttpData\Factories\UriFactory;
use JuanchoSL\RequestListener\Middlewares\HeadMethodMiddleware;
use JuanchoSL\RequestListener\Middlewares\TraceMethodMiddleware;
use JuanchoSL\RequestListener\Tests\UseCaseCommands;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class MiddlewaresTest extends TestCase
{

    public function testTrace()
    {
        $params = http_build_query([
            "required_single" => 'single',
            "required_void" => 1,
            "required_multi" => ['a', 'b', 'c']
        ]);
        $request = (new ServerRequestFactory)->createServerRequest(RequestMethodInterface::METHOD_TRACE, (new UriFactory)->createUri('http://localhost/test?' . $params));

        $result = (new TraceMethodMiddleware)->process($request, new UseCaseCommands);
        $this->assertInstanceOf(ResponseInterface::class, $result);
        $this->assertTrue($result->hasHeader('content-type'));
        $this->assertStringContainsString('message/http', $result->getHeaderLine('content-type'));
    }

    public function testTraceFail()
    {
        $params = http_build_query([
            "required_single" => 'single',
            "required_void" => 1,
            "required_multi" => ['a', 'b', 'c']
        ]);
        $request = (new ServerRequestFactory)->createServerRequest(RequestMethodInterface::METHOD_GET, (new UriFactory)->createUri('http://localhost/test?' . $params));

        $result = (new TraceMethodMiddleware)->process($request, new UseCaseCommands);
        $this->assertInstanceOf(ResponseInterface::class, $result);
        $this->assertTrue($result->hasHeader('content-type'));
        $this->assertStringNotContainsString('message/http', $result->getHeaderLine('content-type'));
    }
    public function testHead()
    {
        $params = http_build_query([
            "required_single" => 'single',
            "required_void" => 1,
            "required_multi" => ['a', 'b', 'c']
        ]);
        $request = (new ServerRequestFactory)->createServerRequest(RequestMethodInterface::METHOD_HEAD, (new UriFactory)->createUri('http://localhost/test?' . $params));

        $result = (new HeadMethodMiddleware)->process($request, new UseCaseCommands);
        $this->assertInstanceOf(ResponseInterface::class, $result);
        $this->assertEquals(0, $result->getBody()->getSize());
    }
    
    public function testHeadFail()
    {
        $params = http_build_query([
            "required_single" => 'single',
            "required_void" => 1,
            "required_multi" => ['a', 'b', 'c']
        ]);
        $request = (new ServerRequestFactory)->createServerRequest(RequestMethodInterface::METHOD_GET, (new UriFactory)->createUri('http://localhost/test?' . $params));
        
        $result = (new HeadMethodMiddleware)->process($request, new UseCaseCommands);
        $this->assertInstanceOf(ResponseInterface::class, $result);
        $this->assertNotEquals(0, $result->getBody()->getSize());
    }
}