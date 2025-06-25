<?php

namespace JuanchoSL\RequestListener\Tests\Integration;

use JuanchoSL\HttpData\Bodies\Creators\UrlencodedCreator;
use JuanchoSL\HttpData\Factories\RequestFactory;
use JuanchoSL\HttpData\Factories\ServerRequestFactory;
use JuanchoSL\HttpData\Factories\StreamFactory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

class WebParserTest extends TestCase
{

    public function testWebGetFromRequest()
    {
        $client = (new RequestFactory)->createRequest('GET', 'http:/localhost/usercase?required_void=&required_multi[]=a&required_multi[]=b&required_multi[]=c');
        $request = (new ServerRequestFactory)->fromRequest($client);
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

    public function testWebPostFromRequest()
    {
        $body = (string) (new UrlencodedCreator)->appendData([
            "required_void" => 1,
            "required_multi" => ['a', 'b', 'c']
        ]);

        $client = (new RequestFactory)->createRequest('POST', 'http:/localhost/usercase')
            ->withAddedHeader('content-type', 'application/x-www-form-urlencoded')
            ->withBody((new StreamFactory)->createStream($body));
        $request = (new ServerRequestFactory)->fromRequest($client);
        $this->assertInstanceOf(ServerRequestInterface::class, $request);
        $attributes = $request->getParsedBody();
        $this->assertIsArray($attributes);
        $this->assertArrayHasKey('required_multi', $attributes);
        $multi = $attributes['required_multi'];
        $this->assertIsArray($multi);
        $this->assertContains('a', $multi);
        $this->assertContains('b', $multi);
        $this->assertContains('c', $multi);
    }
}