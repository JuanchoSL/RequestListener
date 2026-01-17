<?php

namespace JuanchoSL\RequestListener\Tests\Unitary;

use Fig\Http\Message\RequestMethodInterface;
use Fig\Http\Message\StatusCodeInterface;
use JuanchoSL\Exceptions\PreconditionFailedException;
use JuanchoSL\Exceptions\PreconditionRequiredException;
use JuanchoSL\HttpData\Factories\RequestFactory;
use JuanchoSL\HttpData\Factories\ResponseFactory;
use JuanchoSL\HttpData\Factories\ServerRequestFactory;
use JuanchoSL\HttpData\Factories\UriFactory;
use JuanchoSL\RequestListener\Tests\UseCaseCommands;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class UseCasesTest extends TestCase
{

    public function testRequestParser()
    {

        $query = http_build_query([
            "required_single" => 'single',
            "required_void" => 1,
            "required_multi" => ['a', 'b', 'c']
        ]);
        $request = (new RequestFactory)->createRequest(RequestMethodInterface::METHOD_GET, (new UriFactory)->createUri('http://localhost/test?' . $query));
        $request = (new ServerRequestFactory)->fromRequest($request);
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

    public function testGetInvokable()
    {
        $request = (new ServerRequestFactory)->createServerRequest(RequestMethodInterface::METHOD_GET, (new UriFactory)->createUri('http://localhost/test?a=b'));
        $response = (new ResponseFactory)->createResponse(StatusCodeInterface::STATUS_OK);
        $case = new UseCaseCommands;
        $result = $case($request, $response);

        $this->assertInstanceOf(ResponseInterface::class, $result);
    }

    public function testRequestHandler()
    {
        $params = http_build_query([
            "required_single" => 'single',
            "required_multi" => ['a', 'b', 'c'],
            "required_void" => 1
        ]);
        $request = (new ServerRequestFactory)->createServerRequest(RequestMethodInterface::METHOD_GET, (new UriFactory)->createUri('http://localhost/test?' . $params));
        $case = new UseCaseCommands;
        $result = $case->handle($request);

        $this->assertInstanceOf(ResponseInterface::class, $result);
    }
    public function testRequestHandlerFailure()
    {
        $this->expectException(PreconditionRequiredException::class);
        $params = http_build_query([
            "required_void" => 1,
            "required_multi" => ['a', 'b', 'c']
        ]);
        $request = (new ServerRequestFactory)->createServerRequest(RequestMethodInterface::METHOD_GET, (new UriFactory)->createUri('http://localhost/test?' . $params));
        $case = new UseCaseCommands;
        $result = $case->handle($request);

        $this->assertInstanceOf(ResponseInterface::class, $result);
    }
    public function testRequestHandlerParameterFailure()
    {
        $this->expectException(PreconditionFailedException::class);
        $params = http_build_query([
            "required_single" => 'single',
            "required_multi" => ['a', 'b', 'c'],
            "optional_single_int" => 'hello'
        ]);
        $request = (new ServerRequestFactory)->createServerRequest(RequestMethodInterface::METHOD_GET, (new UriFactory)->createUri('http://localhost/test?' . $params));
        $case = new UseCaseCommands;
        $result = $case->handle($request);
        $this->assertInstanceOf(ResponseInterface::class, $result);
    }
    public function testRequestHandlerParameterMultiOneFailure()
    {
        $this->expectException(PreconditionFailedException::class);
        $params = http_build_query([
            "required_single" => 'single',
            "required_multi" => ['a', 'b', 'c'],
            "optional_multi_int" => ['hello']
        ]);
        $request = (new ServerRequestFactory)->createServerRequest(RequestMethodInterface::METHOD_GET, (new UriFactory)->createUri('http://localhost/test?' . $params));
        $case = new UseCaseCommands;
        $result = $case->handle($request);
        $this->assertInstanceOf(ResponseInterface::class, $result);
    }
}