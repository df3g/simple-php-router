<?php
namespace Df3g\Router\Tests;

use Df3g\Router\Request;
use PHPUnit\Framework\TestCase;
use Df3g\Router\Router;

class OptionalParameterTest extends TestCase {
    private $router;

    protected function setUp(): void {
        $this->router = new Router();
    }

    /**
     * Test route with a single optional parameter
     */
    public function testSingleOptionalParameter() {
        $capturedName = null;
        $this->router->addRoute('GET', '/users/{name?}', function(Request $request) use (&$capturedName) {
            $capturedName = $request->getParam('name');

            var_dump($request);
        });

        // Test with parameter
        $this->router->dispatch('GET', '/users/john');
        $this->assertEquals('john', $capturedName, 'Optional parameter should be captured when provided');

        // Reset captured name
        $capturedName = 'previous-value';
        
        // Test without parameter
        $this->router->dispatch('GET', '/users');
        $this->assertNull($capturedName, 'Optional parameter should be null when not provided');
    }

    /**
     * Test route with multiple parameters, some optional
     */
    public function testMultipleOptionalParameters() {
        $capturedCategory = null;
        $capturedTag = null;

        $this->router->addRoute('GET', '/posts/{category}/{tag?}', function($request) use (&$capturedCategory, &$capturedTag) {
            $capturedCategory = $request->getParam('category');
            $capturedTag = $request->getParam('tag');
        });

        // Test with both parameters
        $this->router->dispatch('GET', '/posts/tech/php');
        $this->assertEquals('tech', $capturedCategory, 'First parameter should be captured');
        $this->assertEquals('php', $capturedTag, 'Optional second parameter should be captured');

        // Reset captured values
        $capturedCategory = 'previous-value';
        $capturedTag = 'previous-value';

        // Test with only required parameter
        $this->router->dispatch('GET', '/posts/general');
        $this->assertEquals('general', $capturedCategory, 'First parameter should be captured');
        $this->assertNull($capturedTag, 'Optional second parameter should be null');
    }

    /**
     * Test route with multiple optional parameters
     */
    public function testMultipleConsecutiveOptionalParameters() {
        $capturedYear = null;
        $capturedMonth = null;
        $capturedDay = null;

        $this->router->addRoute('GET', '/archive/{year?}/{month?}/{day?}', 
            function($request) use (&$capturedYear, &$capturedMonth, &$capturedDay) {
                $capturedYear = $request->getParam('year');
                $capturedMonth = $request->getParam('month');
                $capturedDay = $request->getParam('day');
            }
        );

        // Test with all parameters
        $this->router->dispatch('GET', '/archive/2023/12/25');
        $this->assertEquals('2023', $capturedYear, 'First optional parameter should be captured');
        $this->assertEquals('12', $capturedMonth, 'Second optional parameter should be captured');
        $this->assertEquals('25', $capturedDay, 'Third optional parameter should be captured');

        // Reset captured values
        $capturedYear = 'previous-value';
        $capturedMonth = 'previous-value';
        $capturedDay = 'previous-value';

        // Test with partial parameters
        $this->router->dispatch('GET', '/archive/2023/12');
        $this->assertEquals('2023', $capturedYear, 'First optional parameter should be captured');
        $this->assertEquals('12', $capturedMonth, 'Second optional parameter should be captured');
        $this->assertNull($capturedDay, 'Third optional parameter should be null');

        // Reset captured values
        $capturedYear = 'previous-value';
        $capturedMonth = 'previous-value';
        $capturedDay = 'previous-value';

        // Test with single parameter
        $this->router->dispatch('GET', '/archive/2023');
        $this->assertEquals('2023', $capturedYear, 'First optional parameter should be captured');
        $this->assertNull($capturedMonth, 'Second optional parameter should be null');
        $this->assertNull($capturedDay, 'Third optional parameter should be null');

        // Reset captured values
        $capturedYear = 'previous-value';
        $capturedMonth = 'previous-value';
        $capturedDay = 'previous-value';

        // Test without parameters
        $this->router->dispatch('GET', '/archive');
        $this->assertNull($capturedYear, 'First optional parameter should be null');
        $this->assertNull($capturedMonth, 'Second optional parameter should be null');
        $this->assertNull($capturedDay, 'Third optional parameter should be null');
    }
}