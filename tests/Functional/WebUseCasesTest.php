<?php

namespace JuanchoSL\RequestListener\Tests\Functional;

use Fig\Http\Message\StatusCodeInterface;
use JuanchoSL\Exceptions\PreconditionRequiredException;
use JuanchoSL\HttpData\Bodies\Parsers\UrlencodedReader;
use JuanchoSL\HttpData\Factories\ResponseFactory;
use JuanchoSL\HttpData\Factories\StreamFactory;
use JuanchoSL\RequestListener\Engines\WebEngine;
use JuanchoSL\RequestListener\Tests\UseCaseCommands;
use PHPUnit\Framework\TestCase;

class WebUseCasesTest extends TestCase
{

    public function testMissingRequired()
    {
        $this->expectException(PreconditionRequiredException::class);

        $get = [
            'required_void' => '',
            'required_multi' => ['a', 'b', 'c']
        ];
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['SCRIPT_URL'] = '/test';
        $_SERVER['QUERY_STRING'] = http_build_query($get);
        $_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_URL'] . '?' . $_SERVER['QUERY_STRING'];

        $request = WebEngine::parse()->getRequest();
        $response = (new ResponseFactory)->createResponse();
        $command = new UseCaseCommands;
        $result = $command->run($request, $response);
    }

    public function testWithParam()
    {
        $get = [
            'required_single' => 'data',
            'required_void' => '',
            'required_multi' => ['a', 'b', 'c']
        ];
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['SCRIPT_URL'] = '/test';
        $_SERVER['QUERY_STRING'] = http_build_query($get);
        $_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_URL'] . '?' . $_SERVER['QUERY_STRING'];

        $request = WebEngine::parse()->getRequest();
        $response = (new ResponseFactory)->createResponse();
        $command = new UseCaseCommands;
        $result = $command->run($request, $response);
        $this->assertEquals(StatusCodeInterface::STATUS_OK, $result->getStatusCode());
    }
    public function testWithPostBodyUrlEncode()
    {
        $this->assertTrue(true);
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['SCRIPT_URL'] = '/test';
        $_SERVER['REQUEST_URI'] = '/test';
        $_SERVER['HTTP_CONTENT_TYPE'] = 'application/x-www-form-urlencoded';

        $post = [
            'required_single' => 'data',
            'required_void' => '',
            'required_multi' => ['a', 'b', 'c']
        ];
        $body = (new StreamFactory)->createStream(http_build_query($post));
        (new UrlencodedReader($body))->toPostGlobals();
        $request = WebEngine::parse()->getRequest();
        $response = (new ResponseFactory)->createResponse();
        $command = new UseCaseCommands;
        $result = $command->run($request, $response);
        $this->assertEquals(StatusCodeInterface::STATUS_OK, $result->getStatusCode());
    }
    public function testWithPostBodyUrlEncodeFail()
    {
        $this->expectException(PreconditionRequiredException::class);
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['SCRIPT_URL'] = '/test';
        $_SERVER['REQUEST_URI'] = '/test';
        $_SERVER['HTTP_CONTENT_TYPE'] = 'application/x-www-form-urlencoded';

        $post = [
            'required_void' => '',
            'required_multi' => ['a', 'b', 'c']
        ];
        $body = (new StreamFactory)->createStream(http_build_query($post));
        (new UrlencodedReader($body))->toPostGlobals();
        $request = WebEngine::parse()->getRequest();
        $response = (new ResponseFactory)->createResponse();
        $command = new UseCaseCommands;
        $result = $command->run($request, $response);
        $this->assertEquals(StatusCodeInterface::STATUS_OK, $result->getStatusCode());
    }

    public function testWithPutBodyUrlEncode()
    {
        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['SCRIPT_URL'] = '/test';
        $_SERVER['REQUEST_URI'] = '/test';
        $_SERVER['HTTP_CONTENT_TYPE'] = 'application/x-www-form-urlencoded';

        $post = [
            'required_single' => 'data',
            'required_void' => '',
            'required_multi' => ['a', 'b', 'c']
        ];
        $body = (new StreamFactory)->createStream(http_build_query($post));
        (new UrlencodedReader($body))->toPostGlobals();
        $request = WebEngine::parse()->getRequest();
        $response = (new ResponseFactory)->createResponse();
        $command = new UseCaseCommands;
        $result = $command->run($request, $response);
        $this->assertEquals(StatusCodeInterface::STATUS_OK, $result->getStatusCode());
    }
    public function testWithPutBodyUrlEncodeFail()
    {
        $this->expectException(PreconditionRequiredException::class);
        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['SCRIPT_URL'] = '/test';
        $_SERVER['REQUEST_URI'] = '/test';
        $_SERVER['HTTP_CONTENT_TYPE'] = 'application/x-www-form-urlencoded';

        $post = [
            'required_void' => '',
            'required_multi' => ['a', 'b', 'c']
        ];
        $body = (new StreamFactory)->createStream(http_build_query($post));
        (new UrlencodedReader($body))->toPostGlobals();
        $request = WebEngine::parse()->getRequest();
        $response = (new ResponseFactory)->createResponse();
        $command = new UseCaseCommands;
        $result = $command->run($request, $response);
        $this->assertEquals(StatusCodeInterface::STATUS_OK, $result->getStatusCode());
    }

}