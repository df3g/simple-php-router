<?php
namespace Df3g\Router\Tests;

use Df3g\Router\Request;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
    private Request $request;
    private array $testParams;
    private array $testQueryParams;
    private array $testBody;

    protected function setUp(): void
    {
        $this->testParams = ['id' => '123', 'slug' => 'test-post'];
        $this->testQueryParams = ['sort' => 'desc', 'page' => '1'];
        $this->testBody = ['title' => 'New Post', 'content' => 'Test content'];

        $this->request = new Request(
            'POST',
            '/posts/123',
            $this->testParams,
            $this->testQueryParams,
            $this->testBody
        );
    }

    public function testGetParams(): void
    {
        $this->assertEquals($this->testParams, $this->request->getParams());
    }

    public function testGetParam(): void
    {
        $this->assertEquals('123', $this->request->getParam('id'));
        $this->assertEquals('test-post', $this->request->getParam('slug'));
        $this->assertEquals(null, $this->request->getParam('nonexistent'));
        $this->assertEquals('default', $this->request->getParam('nonexistent', 'default'));
    }

    public function testGetMethod(): void
    {
        $this->assertEquals('POST', $this->request->getMethod());
    }

    public function testGetUri(): void
    {
        $this->assertEquals('/posts/123', $this->request->getUri());
    }

    public function testGetQueryParams(): void
    {
        $this->assertEquals($this->testQueryParams, $this->request->getQueryParams());
    }

    public function testGetBody(): void
    {
        $this->assertEquals($this->testBody, $this->request->getBody());
    }

    public function testConstructorWithEmptyArrays(): void
    {
        $request = new Request('GET', '/');
        $this->assertEquals([], $request->getParams());
        $this->assertEquals([], $request->getQueryParams());
        $this->assertEquals([], $request->getBody());
    }

    public function testGetParamWithSpecialCharacters(): void
    {
        $params = ['key@123' => 'value!@#'];
        $request = new Request('GET', '/', $params);
        $this->assertEquals('value!@#', $request->getParam('key@123'));
    }

    /**
     * @dataProvider validMethodProvider
     */
    public function testAcceptsValidHttpMethods(string $method): void
    {
        $request = new Request($method, '/');
        $this->assertEquals($method, $request->getMethod());
    }

    public function validMethodProvider(): array
    {
        return [
            ['GET'],
            ['POST'],
            ['PUT'],
            ['DELETE'],
            ['PATCH'],
            ['OPTIONS'],
            ['HEAD']
        ];
    }
} 