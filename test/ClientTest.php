<?php

/**
 * BitX PHP client
 *
 * https://bitx.co/api
 *
 * THIS CODE AND INFORMATION IS PROVIDED "AS IS" WITHOUT WARRANTY OF ANY
 * KIND, EITHER EXPRESSED OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND/OR FITNESS FOR A PARTICULAR
 * PURPOSE. IT CAN BE DISTRIBUTED FREE OF CHARGE AS LONG AS THIS HEADER
 * REMAINS UNCHANGED.
 */

namespace Bitx;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

class ClientTest extends \PHPUnit_Framework_TestCase
{
    public function testBuildUrl()
    {
        $class = new \ReflectionClass('Bitx\Client');
        $method = $class->getMethod('buildUrl');
        $method->setAccessible(true);
        $client = new Client('foo', 'bar');

        // Test defaults
        $url = $method->invokeArgs($client, ['/foo']);
        $this->assertEquals('https://api.mybitx.com/api/1/foo', $url);
        $url = $method->invokeArgs($client, ['/foo/bar']);
        $this->assertEquals('https://api.mybitx.com/api/1/foo/bar', $url);
        $url = $method->invokeArgs($client, ['foo/bar']);
        $this->assertEquals('https://api.mybitx.com/api/1/foo/bar', $url);
        $url = $method->invokeArgs($client, ['foo?baz=qux']);
        $this->assertEquals('https://api.mybitx.com/api/1/foo?baz=qux', $url);

        // Test change in version
        $client->setVersion('2');
        $url = $method->invokeArgs($client, ['/foo']);
        $this->assertEquals('https://api.mybitx.com/api/2/foo', $url);

        // Test change in base URL
        $client->setUrl('http://www.example.com');
        $url = $method->invokeArgs($client, ['/foo']);
        $this->assertEquals('http://www.example.com/api/2/foo', $url);

        // Test trailing slash is correctly handled
        $client->setUrl('http://www.example.com/');
        $url = $method->invokeArgs($client, ['/foo']);
        $this->assertEquals('http://www.example.com/api/2/foo', $url);
    }

    public function testBuildOptions()
    {
        $class = new \ReflectionClass('Bitx\Client');
        $method = $class->getMethod('buildOptions');
        $method->setAccessible(true);
        $client = new Client('foo', 'bar');

        // Test that auth is returned
        $options = $method->invokeArgs($client, []);
        $this->assertEquals(['auth' => ['foo', 'bar']], $options);

        // Test that provided options are returned
        $options = $method->invokeArgs($client, [['baz' => 'qux']]);
        $this->assertArrayHasKey('baz', $options);
        $this->assertEquals('qux', $options['baz']);

        // Test that if auth is provided, then the provided auth overrides the
        // default auth
        $options = $method->invokeArgs($client, [['auth' => 'foo']]);
        $this->assertEquals('foo', $options['auth']);
    }

    public function testRequest()
    {
        $mock = new MockHandler([
            new Response(200, [], '"foo"'),
            new Response(200, [], '{"error":"Something bad","error_code":123}'),
        ]);
        $handler = HandlerStack::create($mock);
        $guzzle = new HttpClient(['handler' => $handler]);

        $client = new Client('foo', 'bar');
        $client->setUrl('http://www.example.com');
        $client->setVersion('1');
        $client->setClient($guzzle);

        // If the response isn't an object with 'error' set, no exception is
        // thrown
        $this->assertEquals('foo', $client->get('/foo', ['bar' => 'baz']));

        // If the response is an object with 'error' set, an exception will be
        // thrown
        $this->setExpectedException('Bitx\Exception');
        $client->get('/foo', ['bar' => 'baz']);
    }

    public function testRequestMethods()
    {
        $mock = new MockHandler([
            new Response(200, [], '"foo"'),
            new Response(200, [], '"foo"'),
            new Response(200, [], '"foo"'),
            new Response(200, [], '"foo"'),
        ]);
        $container = [];
        $history = Middleware::history($container);
        $handler = HandlerStack::create($mock);
        $handler->push($history);
        $guzzle = new HttpClient(['handler' => $handler]);

        $client = new Client('foo', 'bar');
        $client->setUrl('http://www.example.com');
        $client->setVersion('1');
        $client->setClient($guzzle);

        $client->get('/foo', ['bar' => 'baz']);
        $client->post('/foo', ['bar' => 'baz']);
        $client->put('/foo', ['bar' => 'baz']);
        $client->delete('/foo', ['bar' => 'baz']);

        $this->assertCount(4, $container);

        /** @var Request $req */
        $req = $container[0]['request'];
        $this->assertEquals('GET', $req->getMethod());
        $this->assertEquals('http://www.example.com/api/1/foo?bar=baz', strval($req->getUri()));
        $this->assertEquals('', strval($req->getBody()));

        $req = $container[1]['request'];
        $this->assertEquals('POST', $req->getMethod());
        $this->assertEquals('http://www.example.com/api/1/foo', strval($req->getUri()));
        $this->assertEquals('bar=baz', strval($req->getBody()));

        $req = $container[2]['request'];
        $this->assertEquals('PUT', $req->getMethod());
        $this->assertEquals('http://www.example.com/api/1/foo', strval($req->getUri()));
        $this->assertEquals('bar=baz', strval($req->getBody()));

        $req = $container[3]['request'];
        $this->assertEquals('DELETE', $req->getMethod());
        $this->assertEquals('http://www.example.com/api/1/foo?bar=baz', strval($req->getUri()));
        $this->assertEquals('', strval($req->getBody()));

    }
}

