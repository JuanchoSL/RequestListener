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

## Middlewares

### Work Sequence

#### PRE middlewares

- ValidRouteMiddleware: Check than the selected target is defined and exists
- OptionsMethodMiddleware: Return a response with the available METHODs for the selected target
- TraceMethodMiddleware: Check if the TRACE method is available for the selected target
- ValidMethodMiddleware: Check if the requested method is available, if a HEAD method is selected, we convert to a GET before check it, and remove the body from response if it is availble
- ValidMediaTypeMiddleware: Check if any accepted media-type from the request is available

#### User Defined PRE middlewares

#### Request handler

## Use cases

## Debug
