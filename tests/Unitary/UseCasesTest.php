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
use JuanchoSL\RequestListener\Enums\InputArgument;
use JuanchoSL\RequestListener\Enums\InputOption;
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

    public function testRequestHandlerParameterTypeVoidRequiredFailure()
    {
        $this->expectException(PreconditionRequiredException::class);
        $params = http_build_query([
            "required_single" => 'single',
            "required_multi" => ['a', 'b', 'c']
        ]);
        $request = (new ServerRequestFactory)->createServerRequest(RequestMethodInterface::METHOD_GET, (new UriFactory)->createUri('http://localhost/test?' . $params));
        $case = new UseCaseCommands;
        $case->addArgument('void_required', InputArgument::REQUIRED, InputOption::VOID);
        $result = $case->handle($request);
        $this->assertInstanceOf(ResponseInterface::class, $result);
    }
    public function testRequestHandlerParameterTypeVoidContentFailure()
    {
        $this->expectException(PreconditionFailedException::class);
        $params = http_build_query([
            "optional_void" => 'single',
            "required_single" => 'single',
            "required_multi" => ['a', 'b', 'c']
        ]);
        $request = (new ServerRequestFactory)->createServerRequest(RequestMethodInterface::METHOD_GET, (new UriFactory)->createUri('http://localhost/test?' . $params));
        $case = new UseCaseCommands;
        $result = $case->handle($request);
        $this->assertInstanceOf(ResponseInterface::class, $result);
    }
    public function testRequestHandlerParameterTypeSingleIntRequiredFailure()
    {
        $this->expectException(PreconditionRequiredException::class);
        $params = http_build_query([
            "required_single" => 'single',
            "required_multi" => ['a', 'b', 'c']
        ]);
        $request = (new ServerRequestFactory)->createServerRequest(RequestMethodInterface::METHOD_GET, (new UriFactory)->createUri('http://localhost/test?' . $params));
        $case = new UseCaseCommands;
        $case->addArgument('single_int', InputArgument::REQUIRED, InputOption::SINGLE_INT);
        $result = $case->handle($request);
        $this->assertInstanceOf(ResponseInterface::class, $result);
    }
    public function testRequestHandlerParameterTypeSingleIntContentFailure()
    {
        $this->expectException(PreconditionFailedException::class);
        $params = http_build_query([
            "single_int" => 'single',
            "required_single" => 'single',
            "required_multi" => ['a', 'b', 'c']
        ]);
        $request = (new ServerRequestFactory)->createServerRequest(RequestMethodInterface::METHOD_GET, (new UriFactory)->createUri('http://localhost/test?' . $params));
        $case = new UseCaseCommands;
        $case->addArgument('single_int', InputArgument::REQUIRED, InputOption::SINGLE_INT);
        $result = $case->handle($request);
        $this->assertInstanceOf(ResponseInterface::class, $result);
    }
    public function testRequestHandlerParameterTypeSingleIntAsFloatContentFailure()
    {
        $this->expectException(PreconditionFailedException::class);
        $params = http_build_query([
            "single_int" => '1.1',
            "required_single" => 'single',
            "required_multi" => ['a', 'b', 'c']
        ]);
        $request = (new ServerRequestFactory)->createServerRequest(RequestMethodInterface::METHOD_GET, (new UriFactory)->createUri('http://localhost/test?' . $params));
        $case = new UseCaseCommands;
        $case->addArgument('single_int', InputArgument::REQUIRED, InputOption::SINGLE_INT);
        $result = $case->handle($request);
        $this->assertInstanceOf(ResponseInterface::class, $result);
    }
    public function testRequestHandlerParameterTypeSingleIntContentOk()
    {
        $params = http_build_query([
            "single_int" => '1',
            "required_single" => 'single',
            "required_multi" => ['a', 'b', 'c']
        ]);
        $request = (new ServerRequestFactory)->createServerRequest(RequestMethodInterface::METHOD_GET, (new UriFactory)->createUri('http://localhost/test?' . $params));
        $case = new UseCaseCommands;
        $case->addArgument('single_int', InputArgument::REQUIRED, InputOption::SINGLE_INT);
        $result = $case->handle($request);
        $this->assertInstanceOf(ResponseInterface::class, $result);
    }
    public function testRequestHandlerParameterTypeSingleFloatRequiredFailure()
    {
        $this->expectException(PreconditionRequiredException::class);
        $params = http_build_query([
            "required_single" => 'single',
            "required_multi" => ['a', 'b', 'c']
        ]);
        $request = (new ServerRequestFactory)->createServerRequest(RequestMethodInterface::METHOD_GET, (new UriFactory)->createUri('http://localhost/test?' . $params));
        $case = new UseCaseCommands;
        $case->addArgument('single_float', InputArgument::REQUIRED, InputOption::SINGLE_NUMBER);
        $result = $case->handle($request);
        $this->assertInstanceOf(ResponseInterface::class, $result);
    }
    public function testRequestHandlerParameterTypeSingleFloatContentFailure()
    {
        $this->expectException(PreconditionFailedException::class);
        $params = http_build_query([
            "single_float" => 'single',
            "required_single" => 'single',
            "required_multi" => ['a', 'b', 'c']
        ]);
        $request = (new ServerRequestFactory)->createServerRequest(RequestMethodInterface::METHOD_GET, (new UriFactory)->createUri('http://localhost/test?' . $params));
        $case = new UseCaseCommands;
        $case->addArgument('single_float', InputArgument::REQUIRED, InputOption::SINGLE_NUMBER);
        $result = $case->handle($request);
        $this->assertInstanceOf(ResponseInterface::class, $result);
    }
    public function testRequestHandlerParameterTypeSingleFloatContentOk()
    {
        foreach ([1, 1.1, "1", "1.1"] as $value) {

            $params = http_build_query([
                "single_float" => $value,
                "required_single" => 'single',
                "required_multi" => ['a', 'b', 'c']
            ]);
            $request = (new ServerRequestFactory)->createServerRequest(RequestMethodInterface::METHOD_GET, (new UriFactory)->createUri('http://localhost/test?' . $params));
            $case = new UseCaseCommands;
            $case->addArgument('single_float', InputArgument::REQUIRED, InputOption::SINGLE_NUMBER);
            $result = $case->handle($request);
            $this->assertInstanceOf(ResponseInterface::class, $result);
        }
    }
    public function testRequestHandlerParameterTypeBoolRequiredFailure()
    {
        $this->expectException(PreconditionRequiredException::class);
        $params = http_build_query([
            "required_single" => 'single',
            "required_multi" => ['a', 'b', 'c']
        ]);
        $request = (new ServerRequestFactory)->createServerRequest(RequestMethodInterface::METHOD_GET, (new UriFactory)->createUri('http://localhost/test?' . $params));
        $case = new UseCaseCommands;
        $case->addArgument('boolean', InputArgument::REQUIRED, InputOption::BOOL);
        $result = $case->handle($request);
        $this->assertInstanceOf(ResponseInterface::class, $result);
    }
    public function testRequestHandlerParameterTypeBoolContentFailure()
    {
        $this->expectException(PreconditionFailedException::class);
        $params = http_build_query([
            "boolean" => 'single',
            "required_single" => 'single',
            "required_multi" => ['a', 'b', 'c']
        ]);
        $request = (new ServerRequestFactory)->createServerRequest(RequestMethodInterface::METHOD_GET, (new UriFactory)->createUri('http://localhost/test?' . $params));
        $case = new UseCaseCommands;
        $case->addArgument('boolean', InputArgument::REQUIRED, InputOption::BOOL);
        $result = $case->handle($request);
        $this->assertInstanceOf(ResponseInterface::class, $result);
    }
    public function testRequestHandlerParameterTypeBoolContentOk()
    {
        foreach ([true, 1, "On", "1", "true", false, 0, "Off", "0", "false"] as $value) {

            $params = http_build_query([
                "boolean" => $value,
                "required_single" => 'single',
                "required_multi" => ['a', 'b', 'c']
            ]);
            $request = (new ServerRequestFactory)->createServerRequest(RequestMethodInterface::METHOD_GET, (new UriFactory)->createUri('http://localhost/test?' . $params));
            $case = new UseCaseCommands;
            $case->addArgument('boolean', InputArgument::REQUIRED, InputOption::BOOL);
            $result = $case->handle($request);
            $this->assertInstanceOf(ResponseInterface::class, $result);
        }
    }
}