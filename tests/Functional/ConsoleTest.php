<?php

namespace JuanchoSL\RequestListener\Tests\Functional;

use Fig\Http\Message\StatusCodeInterface;
use JuanchoSL\Exceptions\DestinationUnreachableException;
use JuanchoSL\Exceptions\NotFoundException;
use JuanchoSL\Exceptions\PreconditionRequiredException;
use JuanchoSL\Logger\Composers\TextComposer;
use JuanchoSL\Logger\Logger;
use JuanchoSL\Logger\Repositories\FileRepository;
use JuanchoSL\RequestListener\Application;
use JuanchoSL\RequestListener\Handlers\MyErrorHandler;
use JuanchoSL\RequestListener\Tests\UseCaseCommands;
use JuanchoSL\Terminal\Console;
use JuanchoSL\Terminal\Tests\UseCaseCommand;
use PHPUnit\Framework\TestCase;

class ConsoleTest extends TestCase
{

    protected Application $app;

    protected function prepare(): void
    {
        $logger = new Logger((new FileRepository(implode(DIRECTORY_SEPARATOR, [dirname(__FILE__, 3), 'logs', 'tests.log'])))->setComposer(new TextComposer));
        $errorHandler = (new MyErrorHandler)->setLogErrors(true)->setLogErrorsDetails(true)->setDisplayErrorsDetails(true);
        $errorHandler->setLogger($logger);

        $app = new Application();
        $app->setErrorHandler($errorHandler);
        $app->get('/usecase', UseCaseCommands::class);
        $this->app = $app;
    }

    protected function tearDown(): void
    {
        unset($this->app);
    }

    /*
    public function testMissingCommand()
    {
        //$this->expectException(NotFoundException::class);
        $_SERVER['argv'] = [];
        $_SERVER['argv'][] = '--required_void';
        $_SERVER['argv'][] = '--required_multi';
        $_SERVER['argv'][] = 'a';
        $_SERVER['argv'][] = 'b';
        $_SERVER['argv'][] = 'c';

        $code = 0;
        set_error_handler(function (...$args) use (&$code) {
            // your code
            $code = error_get_last();
            echo "<pre>{$code}: " . print_r($args, true);
            // your code
        }, E_ALL);
        // trigger error your code
        $this->prepare();
        $this->app->runWithoutExit();
        //restore_error_handler();
        $this->assertEquals(NotFoundException::CODE, $code);
    }
    public function testMissingCommand()
    {
        $this->expectException(NotFoundException::class);
        $_SERVER['argv'] = [];
        $_SERVER['argv'][] = '--required_void';
        $_SERVER['argv'][] = '--required_multi';
        $_SERVER['argv'][] = 'a';
        $_SERVER['argv'][] = 'b';
        $_SERVER['argv'][] = 'c';
        $this->prepare();
        $code = $this->app->runWithoutExit();
    }
    */

    public function testMissingRequired()
    {
        $this->expectException(PreconditionRequiredException::class);
        $_SERVER['argv'] = [];
        $_SERVER['argv'][] = 'entrypoint';
        $_SERVER['argv'][] = 'usecase';
        $_SERVER['argv'][] = '--required_void';
        $_SERVER['argv'][] = '--required_multi';
        $_SERVER['argv'][] = 'a';
        $_SERVER['argv'][] = 'b';
        $_SERVER['argv'][] = 'c';

        $this->prepare();
        $code = $this->app->runWithoutExit();
        //$this->assertEquals(0, $this->getStatus());
        $this->assertEquals(StatusCodeInterface::STATUS_OK, $code);
    }

    public function testWithEquals()
    {
        $_SERVER['argv'] = [];
        $_SERVER['argv'][] = 'entrypoint';
        $_SERVER['argv'][] = '/usecase';
        $_SERVER['argv'][] = '--required_single=./';
        $_SERVER['argv'][] = '--required_void';
        $_SERVER['argv'][] = '--required_multi';
        $_SERVER['argv'][] = 'a';
        $_SERVER['argv'][] = 'b';
        $_SERVER['argv'][] = 'c';

        $this->prepare();
        $code = $this->app->runWithoutExit();
        //$this->assertEquals(0, $this->getStatus());
        $this->assertEquals(StatusCodeInterface::STATUS_OK, $code);
    }
    public function testWithParam()
    {
        $_SERVER['argv'] = [];
        $_SERVER['argv'][] = 'entrypoint';
        $_SERVER['argv'][] = 'usecase';
        $_SERVER['argv'][] = '--required_single';
        $_SERVER['argv'][] = './';
        $_SERVER['argv'][] = '--required_void';
        $_SERVER['argv'][] = '--required_multi';
        $_SERVER['argv'][] = 'a';
        $_SERVER['argv'][] = 'b';
        $_SERVER['argv'][] = 'c';

        $this->prepare();
        $code = $this->app->runWithoutExit();
        //$this->assertEquals(0, $this->getStatus());
        $this->assertEquals(StatusCodeInterface::STATUS_OK, $code);
    }

}