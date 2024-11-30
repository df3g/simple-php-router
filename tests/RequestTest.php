<?php
namespace Df3g\Router\Tests;

use PHPUnit\Framework\TestCase;
use Df3g\Router\Request;

class RequestTest extends TestCase {
    public function testBasicRequestProperties() {
        $request = new Request('GET', '/test', ['id' => '123']);
        
        $this->assertEquals('GET', $request->getMethod());
        $this->assertEquals('/test', $request->getUri());
        $this->assertEquals(['id' => '123'], $request->getParams());
        $this->assertEquals('123', $request->getParam('id'));
        $this->assertNull($request->getParam('non_existent'));
        $this->assertEquals('default', $request->getParam('non_existent', 'default'));
    }

    public function testQueryParameters() {
        $request = new Request('GET', '/test?name=John&age=25');
        
        $this->assertEquals(['name' => 'John', 'age' => '25'], $request->getQuery());
        $this->assertEquals('John', $request->getQueryParam('name'));
        $this->assertEquals('25', $request->getQueryParam('age'));
        $this->assertNull($request->getQueryParam('non_existent'));
        $this->assertEquals('default', $request->getQueryParam('non_existent', 'default'));
    }

    public function testCustomQueryParameters() {
        $request = new Request('GET', '/test', [], ['custom' => 'value']);
        
        $this->assertEquals(['custom' => 'value'], $request->getQuery());
        $this->assertEquals('value', $request->getQueryParam('custom'));
    }

    public function testCustomHeaders() {
        $headers = [
            'accept' => 'application/json',
            'x-custom' => 'test'
        ];
        $request = new Request('GET', '/test', [], null, $headers);
        
        $this->assertEquals($headers, $request->getHeaders());
        $this->assertEquals('application/json', $request->getHeader('accept'));
        $this->assertEquals('test', $request->getHeader('x-custom'));
        $this->assertNull($request->getHeader('non-existent'));
    }

    public function testJsonBody() {
        $body = '{"key":"value"}';
        $headers = ['content-type' => 'application/json'];
        $request = new Request('POST', '/test', [], null, $headers, $body);
        
        $this->assertTrue($request->isJson());
        $this->assertEquals($body, $request->getBody());
        $this->assertEquals(['key' => 'value'], $request->getJson());
    }

    public function testNonJsonBody() {
        $body = 'plain text';
        $headers = ['content-type' => 'text/plain'];
        $request = new Request('POST', '/test', [], null, $headers, $body);
        
        $this->assertFalse($request->isJson());
        $this->assertEquals($body, $request->getBody());
        $this->assertNull($request->getJson());
    }

    public function testMethodNormalization() {
        $request = new Request('get', '/test');
        $this->assertEquals('GET', $request->getMethod());
        
        $request = new Request('Post', '/test');
        $this->assertEquals('POST', $request->getMethod());
    }

    public function testHeaderCaseInsensitivity() {
        $headers = ['Content-Type' => 'application/json'];
        $request = new Request('GET', '/test', [], null, $headers);
        
        $this->assertEquals('application/json', $request->getHeader('content-type'));
        $this->assertEquals('application/json', $request->getHeader('CONTENT-TYPE'));
        $this->assertEquals('application/json', $request->getHeader('Content-Type'));
    }
}