<?php
namespace Dgomespt\Router\Tests;

use Dgomespt\Router\Request;
use PHPUnit\Framework\TestCase;
use Dgomespt\Router\Router;

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
        $this->router->addRoute('GET', '/', function() use (&$called) {
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

        $this->router->addRoute('GET', 'test', function() use (&$getCallbackCalled) {
            $getCallbackCalled = true;
        });

        $this->router->addRoute('POST', 'test', function() use (&$postCallbackCalled) {
            $postCallbackCalled = true;
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
        $this->router->addRoute('GET', 'test-route', function() use (&$called) {
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
        $this->router->addRoute('GET', 'return-test', function() {
            return 'test-value';
        });

        $result = $this->router->dispatch('GET', 'return-test');
        $this->assertEquals('test-value', $result, 'Route handler return value should be preserved');
    }

    /**
     * Test parameter validation by regex pattern matching
     */
    public function testParameterValidation() {
        $capturedId = null;
        $this->router->addRoute('GET', 'users/{id}', function(Request $request) use (&$capturedId) {
            $capturedId = $request->getParam('id');
            if (!is_numeric($capturedId)) {
                $capturedId = null;
            }
        });

        $this->router->dispatch('GET', 'users/123');
        $this->assertEquals('123', $capturedId, 'Numeric parameter should be matched');

        // Reset captured value
        $capturedId = null;
        $this->router->dispatch('GET', 'users/abc');
        $this->assertNull($capturedId, 'Non-numeric parameter should not match');
    }

    /**
     * Test custom not found handler
     */
    public function testCustomNotFoundHandler() {
        $notFoundCalled = false;
        $this->router->setNotFoundHandler(function() use (&$notFoundCalled) {
            $notFoundCalled = true;
        });

        $this->router->dispatch('GET', '/non-existent-route');
        $this->assertTrue($notFoundCalled, 'Custom not found handler should be called for non-matching routes');
    }
}