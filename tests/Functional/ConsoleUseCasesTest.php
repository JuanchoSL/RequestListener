<?php

namespace JuanchoSL\RequestListener\Tests\Functional;

use Fig\Http\Message\StatusCodeInterface;
use JuanchoSL\Exceptions\NotFoundException;
use JuanchoSL\Exceptions\PreconditionRequiredException;
use JuanchoSL\HttpData\Factories\ResponseFactory;
use JuanchoSL\RequestListener\Engines\ConsoleEngine;
use JuanchoSL\RequestListener\Tests\UseCaseCommands;
use PHPUnit\Framework\TestCase;

class ConsoleUseCasesTest extends TestCase
{

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

        $request = ConsoleEngine::parse();

        $response = (new ResponseFactory)->createResponse();
        $command = new UseCaseCommands;
        $result = $command->run($request->getRequest(), $response);

    }

    public function testWithEquals()
    {
        $_SERVER['argv'] = [];
        $_SERVER['argv'][] = 'entrypoint';
        $_SERVER['argv'][] = 'usecase';
        $_SERVER['argv'][] = '--required_single=./';
        $_SERVER['argv'][] = '--required_void';
        $_SERVER['argv'][] = '--required_multi';
        $_SERVER['argv'][] = 'a';
        $_SERVER['argv'][] = 'b';
        $_SERVER['argv'][] = 'c';
        $request = ConsoleEngine::parse()->getRequest();
        $response = (new ResponseFactory)->createResponse();
        $command = new UseCaseCommands;
        $result = $command->run($request, $response);
        $this->assertEquals(StatusCodeInterface::STATUS_OK, $result->getStatusCode());
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
        $request = ConsoleEngine::parse()->getRequest();
        $response = (new ResponseFactory)->createResponse();
        $command = new UseCaseCommands;
        $result = $command->run($request, $response);
        $this->assertEquals(StatusCodeInterface::STATUS_OK, $result->getStatusCode());
    }

}