<?php
namespace Df3g\Router\Tests;

use PHPUnit\Framework\TestCase;
use Df3g\Router\Router;
use Df3g\Router\Request;

class RouterTest extends TestCase {
    private $router;

    protected function setUp(): void {
        $this->router = new Router();
    }

    /**
     * Test basic route matching
     */
    public function testBasicRouteMatching() {
        $called = false;
        $this->router->addRoute('GET', '/', function(Request $request) use (&$called) {
            $called = true;
        });

        $this->router->dispatch('GET', '/');
        $this->assertTrue($called, 'Root route should be matched and handler called');
    }

    /**
     * Test route with single parameter
     */
    public function testRouteWithSingleParameter() {
        $capturedId = null;
        $this->router->addRoute('GET', 'users/{id}', function(Request $request) use (&$capturedId) {
            $capturedId = $request->getParam('id');
        });

        $this->router->dispatch('GET', 'users/123');
        $this->assertEquals('123', $capturedId, 'Route parameter should be correctly captured');
    }

    /**
     * Test route with multiple parameters
     */
    public function testRouteWithMultipleParameters() {
        $capturedCategory = null;
        $capturedSlug = null;
        $this->router->addRoute('GET', 'posts/{category}/{slug}', function(Request $request) use (&$capturedCategory, &$capturedSlug) {
            $capturedCategory = $request->getParam('category');
            $capturedSlug = $request->getParam('slug');
        });

        $this->router->dispatch('GET', 'posts/tech/awesome-article');
        $this->assertEquals('tech', $capturedCategory, 'First route parameter should be correctly captured');
        $this->assertEquals('awesome-article', $capturedSlug, 'Second route parameter should be correctly captured');
    }

    /**
     * Test HTTP method differentiation
     */
    public function testHttpMethodDifferentiation() {
        $getCallbackCalled = false;
        $postCallbackCalled = false;

        $this->router->addRoute('GET', 'test', function(Request $request) use (&$getCallbackCalled) {
            $getCallbackCalled = true;
            $this->assertEquals('GET', $request->getMethod());
        });

        $this->router->addRoute('POST', 'test', function(Request $request) use (&$postCallbackCalled) {
            $postCallbackCalled = true;
            $this->assertEquals('POST', $request->getMethod());
        });

        $this->router->dispatch('GET', 'test');
        $this->assertTrue($getCallbackCalled, 'GET route should be called for GET method');
        $this->assertFalse($postCallbackCalled, 'POST route should not be called for GET method');

        // Reset flags
        $getCallbackCalled = false;
        $this->router->dispatch('POST', 'test');
        $this->assertFalse($getCallbackCalled, 'GET route should not be called for POST method');
        $this->assertTrue($postCallbackCalled, 'POST route should be called for POST method');
    }

    /**
     * Test route matching with trailing slashes
     */
    public function testRouteMatchingWithTrailingSlashes() {
        $called = false;
        $this->router->addRoute('GET', 'test-route', function(Request $request) use (&$called) {
            $called = true;
        });

        $this->router->dispatch('GET', '/test-route/');
        $this->assertTrue($called, 'Route should match with or without trailing slashes');

        $called = false;
        $this->router->dispatch('GET', 'test-route/');
        $this->assertTrue($called, 'Route should match with or without leading slashes');
    }

    /**
     * Test return value from route handler
     */
    public function testRouteHandlerReturnValue() {
        $this->router->addRoute('GET', 'return-test', function(Request $request) {
            return 'test-value';
        });

        $result = $this->router->dispatch('GET', 'return-test');
        $this->assertEquals('test-value', $result, 'Route handler return value should be preserved');
    }

    /**
     * Test parameter validation by regex pattern matching
     */
    public function testParameterValidation() {
        $matchedNumeric = false;
        $this->router->addRoute('GET', 'users/{id}', function(Request $request) use (&$matchedNumeric) {
            $id = $request->getParam('id');
            $matchedNumeric = is_numeric($id);
        });

        $this->router->dispatch('GET', 'users/123');
        $this->assertTrue($matchedNumeric, 'Numeric parameter should be matched');

        $matchedNumeric = true;
        $this->router->dispatch('GET', 'users/abc');
        $this->assertFalse($matchedNumeric, 'Non-numeric parameter should not match');
    }

    /**
     * Test custom not found handler
     */
    public function testCustomNotFoundHandler() {
        $notFoundCalled = false;
        $this->router->setNotFoundHandler(function(Request $request) use (&$notFoundCalled) {
            $notFoundCalled = true;
            $this->assertNotNull($request->getMethod());
            $this->assertNotNull($request->getUri());
        });

        $this->router->dispatch('GET', '/non-existent-route');
        $this->assertTrue($notFoundCalled, 'Custom not found handler should be called for non-matching routes');
    }

    /**
     * Test Request object functionality
     */
    public function testRequestObject() {
        $request = null;
        $this->router->addRoute('GET', 'test/{param1}/{param2?}', function(Request $req) use (&$request) {
            $request = $req;
            var_dump($req->getParams());
        });

        $this->router->dispatch('GET', 'test/value1/value2');
        
        $this->assertNotNull($request);
        $this->assertEquals('GET', $request->getMethod());
        $this->assertEquals('test/value1/value2', $request->getUri());
        $this->assertEquals('value1', $request->getParam('param1'));
        $this->assertEquals('value2', $request->getParam('param2'));
        $this->assertNull($request->getParam('non_existent'));
        $this->assertEquals('default', $request->getParam('non_existent', 'default'));
        
        $params = $request->getParams();
        $this->assertIsArray($params);
        $this->assertArrayHasKey('param1', $params);
        $this->assertArrayHasKey('param2', $params);
    }
}