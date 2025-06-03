<?php

namespace JuanchoSL\RequestListener\Tests\Integration;

use Fig\Http\Message\RequestMethodInterface;
use Fig\Http\Message\StatusCodeInterface;
use JuanchoSL\Exceptions\DestinationUnreachableException;
use JuanchoSL\Exceptions\MethodNotAllowedException;
use JuanchoSL\Exceptions\NotFoundException;
use JuanchoSL\Exceptions\PreconditionRequiredException;
use JuanchoSL\HttpData\Bodies\Parsers\UrlencodedReader;
use JuanchoSL\HttpData\Factories\RequestFactory;
use JuanchoSL\HttpData\Factories\ServerRequestFactory;
use JuanchoSL\HttpData\Factories\StreamFactory;
use JuanchoSL\Logger\Composers\TextComposer;
use JuanchoSL\Logger\Logger;
use JuanchoSL\Logger\Repositories\FileRepository;
use JuanchoSL\RequestListener\Application;
use JuanchoSL\RequestListener\Engines\TestsEngine;
use JuanchoSL\RequestListener\Engines\WebEngine;
use JuanchoSL\RequestListener\Handlers\MyErrorHandler;
use JuanchoSL\RequestListener\Tests\UseCaseCommands;
use PHPUnit\Framework\TestCase;

class WebappTest extends TestCase
{

    protected Application $app;

    protected function prepare(): void
    {
        $logger = new Logger((new FileRepository(implode(DIRECTORY_SEPARATOR, [dirname(__FILE__, 3), 'logs', 'tests.log'])))->setComposer(new TextComposer));
        $errorHandler = (new MyErrorHandler)->setLogErrors(true)->setLogErrorsDetails(true)->setDisplayErrorsDetails(true);
        $errorHandler->setLogger($logger);
        $app = new Application(WebEngine::parse());
        $app->setErrorHandler($errorHandler);
        $app->get('/test', UseCaseCommands::class);
        $app->post('/test', UseCaseCommands::class);
        $app->put('/test', UseCaseCommands::class);
        $this->app = $app;
    }

    protected function tearDown(): void
    {
        unset($this->app);
    }
/*
    public function testMissingRequired()
    {
        $this->expectException(PreconditionRequiredException::class);

        $get = [
            'required_void' => '',
            'required_multi' => ['a', 'b', 'c']
        ];

        $request = (new RequestFactory)->createRequest(RequestMethodInterface::METHOD_GET, 'http://localhost/test?' . http_build_query($get));
        $server_request = (new ServerRequestFactory)->fromRequest($request);
        $this->prepare();
        $code = $this->app->runWithoutExit();
        $this->assertEquals(0, $this->getStatus());
        $this->assertEquals(StatusCodeInterface::STATUS_OK, $code);
    }
*/
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
        //$_SERVER['QUERY_STRING'] = 'required_single=data&required_void=&required_multi[]=a&required_multi[]=b&required_multi[]=c';
        $_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_URL'] . '?' . $_SERVER['QUERY_STRING'];

        $this->prepare();
        $code = $this->app->runWithoutExit();
        //$this->assertEquals(0, $this->getStatus());
        $this->assertEquals(StatusCodeInterface::STATUS_OK, $code);
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
        $this->prepare();
        $code = $this->app->runWithoutExit();
        $this->assertEquals(StatusCodeInterface::STATUS_OK, $code);
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
        $this->prepare();
        $code = $this->app->runWithoutExit();
        $this->assertEquals(StatusCodeInterface::STATUS_PRECONDITION_REQUIRED, $code);
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
        $this->prepare();
        $code = $this->app->runWithoutExit();
        $this->assertEquals(StatusCodeInterface::STATUS_OK, $code);
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
        $this->prepare();
        $code = $this->app->runWithoutExit();
        $this->assertEquals(StatusCodeInterface::STATUS_PRECONDITION_REQUIRED, $code);
    }
    public function testWithPatchBodyUrlEncodeFail()
    {
        //$this->expectException(MethodNotAllowedException::class);
        $_SERVER['REQUEST_METHOD'] = 'PATCH';
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
        $this->prepare();
        $code = $this->app->runWithoutExit();
        $this->assertEquals(StatusCodeInterface::STATUS_METHOD_NOT_ALLOWED, $code);
    }

}