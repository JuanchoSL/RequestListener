# RequestListener

## Description

This library groups the definitions of the different elements involved in data transmissions such as HTTP, applying the interfaces defined in PSRS 7 and 17, adding the extra objects used and ensuring compliance with the requirements that allow to give the necessary stability To our application, being able to implement for third -party bookstores without the need to adapt our business logic to a new implementation that could modify its consumption or internal structure.

## Install

```bash
composer require juanchosl/requestlistener
```

## Engines

The library accept two types of uses

- http requests
- console commands

Both systems parse the SERVER globals and create a ServerRequestInterface compatible element in order to unify his use across all the work flow. The user needs to prepare the response format according to the requested type.
You can create you own Engine parser implementing the JuanchoSL\Contracts\EnginesInterface, only needs:

- static parse method, passing the vars to parse. Returns an EngineInterface entity
- getRequest, to retrieve the parsed ServerRequest
- sendMessage, to perform and send the compatible ResponseInterface message

### HTTP requests

Create a request according to the PSR-7, the request is builded using PHP superglobals, for body contents, if content-type header is setted as application/x-www-form-urlencoded or multipart/form-data, the superglobal POST is used, for other media-types, the 'php://input' contents is readed and putted as body and parsed as a DataTranster entity and putted into parsedBody

### Console commands

Create a request according to the PSR-7, the request is builded using SERVER['args'] superglobal for fill Query params and STDIN for body contents, the arguments need to have the next format:
The parameter name needs to start with --, then can assign values from:

- concat with an equals sign (--name=value)
- put the value after parameter (--name value)
- if is a void parameter that don't need value, just write the parameter name (--void_parameter)
- if is a multiple values parameter, use the name all times that you need pass a value or write the name and value multiple times as a single value
  - --multiple=value1 --multiple=value2
  - --multiple value1 --multiple value2
  - --multiple value1 value2

## Application

### Middlewares

#### PRE middlewares

- ValidRouteMiddleware: Check than the selected target is defined and exists
- OptionsMethodMiddleware: Return a response with the available METHODs for the selected target
- TraceMethodMiddleware: Check if the TRACE method is available for the selected target
- ValidMethodMiddleware: Check if the requested method is available, if a HEAD method is selected, we convert to a GET before check it, and remove the body from response if it is availble
- ValidMediaTypeMiddleware: Check if any accepted media-type from the request is available

#### User Defined PRE middlewares

You can create and use Middlewares that implementing the PSR-15 Interfaces, according the [Psr\Http\Server\MiddlewareInterface](https://www.php-fig.org/psr/psr-15/)

```php
<?php declare(strict_types=1);

namespace Src\Infrastructure\Middlewares;

use Fig\Http\Message\StatusCodeInterface;
use JuanchoSL\HttpData\Factories\ResponseFactory;
use JuanchoSL\Validators\Types\Strings\StringValidations;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;

class AuthorizationMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $ip = filter_input(INPUT_SERVER, 'SERVER_ADDR');
        if (!(new StringValidations)->isNotEmpty()->isIpV4()->isValueContaining('192.168.0.1')->getResult((string)$ip)) {
            return (new ResponseFactory)->createResponse(StatusCodeInterface::STATUS_UNAUTHORIZED);
        }
        return $handler->handle($request);
    }
}
```

### Request handlers

According the PSR-15, you can create and execute your own [RequestHandlers](https://www.php-fig.org/psr/psr-15/#21-psrhttpserverrequesthandlerinterface "RequestHandlers")

```php
<?php declare(strict_types=1);

namespace JuanchoSL\RequestListener\Commands;

use Fig\Http\Message\StatusCodeInterface;
use JuanchoSL\DataTransfer\Factories\DataConverterFactory;
use JuanchoSL\DataTransfer\Factories\DataTransferFactory;
use JuanchoSL\HttpData\Factories\ResponseFactory;
use JuanchoSL\HttpData\Factories\StreamFactory;
use JuanchoSL\HttpHeaders\ContentType;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ConvertHandler implements RequestHandlerInterface
{

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $body_contents = DataTransferFactory::byMimeType((string) $request->getBody(), $request->getHeader('content-type'));
        $content_type = ($request->hasHeader('accept')) ? $request->getHeader('accept') : ContentType::get($request->getQueryParams()['format']);
        $body = DataConverterFactory::asMimeType($body_contents, $content_type);

        return (new ResponseFactory)->createResponse(StatusCodeInterface::STATUS_OK)
            ->withAddedHeader('content-type', $content_type)
            ->withBody((new StreamFactory)->createStream($body));
    }
}
```

### Use cases

The Application system, group the routing, methods access, and callables to be executed when the rules has been accomplished. Into the entrypoint, you need to prepare endpoints and his rules to be executed.

When you extend the UseCases provided class, a configure method is required, in order to set the valid parameters inorder to perform an autovalidation

The callables can be:
- A Handler implementing the PSR-15 interface
- A command, extending the UseCases provided class and implementing an __invoke method with the params:
    - ServerRequestInterface
    - ResponseInterface
- A callable with format [Class, 'method_to_call']

```php
<?php

use JuanchoSL\HttpData\Factories\ResponseFactory;
use JuanchoSL\RequestListener\Commands\HelpCommands;
use JuanchoSL\RequestListener\Engines\ConsoleEngine;
use JuanchoSL\RequestListener\Entities\Router;
use JuanchoSL\RequestListener\Handlers\NotAllowedResponseHandler;
use JuanchoSL\RequestListener\Handlers\QueueRequestHandler;
use JuanchoSL\Logger\Composers\TextComposer;
use JuanchoSL\Logger\Logger;
use JuanchoSL\Logger\Repositories\FileRepository;
use JuanchoSL\RequestListener\Application;
use JuanchoSL\RequestListener\Handlers\MyErrorHandler;
use JuanchoSL\RequestListener\Middlewares\AuthenticationMiddleware;
use JuanchoSL\RequestListener\Middlewares\OutputCompressionMiddleware;
use JuanchoSL\RequestListener\Middlewares\ValidRouteMiddleware;
use JuanchoSL\RequestListener\Tests\UseCaseCommands;

date_default_timezone_set("Europe/Madrid");

include_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
$logger = new Logger((new FileRepository(implode(DIRECTORY_SEPARATOR, ['..', 'logs', 'error.log'])))->setComposer(new TextComposer));
$errorHandler = (new MyErrorHandler)->setLogErrors(true)->setLogErrorsDetails(true)->setDisplayErrorsDetails(true);
$errorHandler->setLogger($logger);

$app = new Application();
$app->setErrorHandler($errorHandler);
$app->addMiddleware(new AuthenticationMiddleware);
$app->get('/help', HelpCommands::class);
$app->post('/convert', ConvertCommand::class);
$app->put('/convert', ConvertHandler::class);
$app->addMiddleware(new OutputCompressionMiddleware);
$app->run(); //call to run, performs an exit in order to process shutdown_functions and exit code from console use
```

#### ExampleCommand

```php
<?php declare(strict_types=1);

namespace JuanchoSL\RequestListener\Commands;

use Fig\Http\Message\StatusCodeInterface;
use JuanchoSL\DataTransfer\Factories\DataConverterFactory;
use JuanchoSL\DataTransfer\Factories\DataTransferFactory;
use JuanchoSL\HttpData\Factories\StreamFactory;
use JuanchoSL\HttpHeaders\ContentType;
use JuanchoSL\RequestListener\Enums\InputArgument;
use JuanchoSL\RequestListener\Enums\InputOption;
use JuanchoSL\RequestListener\UseCases;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ConvertCommand extends UseCases
{
    protected function configure(): void
    {
        $this->addArgument('format', InputArgument::OPTIONAL, InputOption::SINGLE);
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $body_contents = DataTransferFactory::byMimeType((string) $request->getBody(), $request->getHeader('content-type'));
        $content_type = ($request->hasHeader('accept')) ? $request->getHeader('accept') : ContentType::get($request->getQueryParams()['format']);
        $body = DataConverterFactory::asMimeType($body_contents, $content_type);

        return $response
            ->withStatus(StatusCodeInterface::STATUS_OK)
            ->withAddedHeader('content-type', $content_type)
            ->withBody((new StreamFactory)->createStream($body));
    }
}
```

## Debug
