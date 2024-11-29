<?php
namespace Df3g\Router\Tests;

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
        $this->router->addRoute('GET', 'users/{name?}', function($name = null) use (&$capturedName) {
            $capturedName = $name;
        });

        // Test with parameter
        $this->router->dispatch('GET', 'users/john');
        $this->assertEquals('john', $capturedName, 'Optional parameter should be captured when provided');

        // Test without parameter
        $this->router->dispatch('GET', 'users');
        $this->assertEmpty($capturedName, 'Optional parameter should be null when not provided');
    }

    /**
     * Test route with multiple parameters, some optional
     */
    public function testMultipleOptionalParameters() {
        $capturedCategory = null;
        $capturedTag = null;

        $this->router->addRoute('GET', 'posts/{category}/{tag?}', function($category, $tag = null) use (&$capturedCategory, &$capturedTag) {
            $capturedCategory = $category;
            $capturedTag = $tag;
        });

        // Test with both parameters
        $this->router->dispatch('GET', 'posts/tech/php');
        $this->assertEquals('tech', $capturedCategory, 'First parameter should be captured');
        $this->assertEquals('php', $capturedTag, 'Optional second parameter should be captured');

        // Test with only required parameter
        $capturedTag = 'previous';
        $this->router->dispatch('GET', 'posts/general');
        $this->assertEquals('general', $capturedCategory, 'First parameter should be captured');
        $this->assertEmpty($capturedTag, 'Optional second parameter should be null');
    }

    /**
     * Test route with multiple optional parameters
     */
    public function testMultipleConsecutiveOptionalParameters() {
        $capturedYear = null;
        $capturedMonth = null;
        $capturedDay = null;

        $this->router->addRoute('GET', 'archive/{year?}/{month?}/{day?}', 
            function($year = null, $month = null, $day = null) use (&$capturedYear, &$capturedMonth, &$capturedDay) {
                $capturedYear = $year;
                $capturedMonth = $month;
                $capturedDay = $day;
            }
        );

        // Test with all parameters
        $this->router->dispatch('GET', 'archive/2023/12/25');
        $this->assertEquals('2023', $capturedYear, 'First optional parameter should be captured');
        $this->assertEquals('12', $capturedMonth, 'Second optional parameter should be captured');
        $this->assertEquals('25', $capturedDay, 'Third optional parameter should be captured');

        // Test with partial parameters
        $this->router->dispatch('GET', 'archive/2023/12');
        $this->assertEquals('2023', $capturedYear, 'First optional parameter should be captured');
        $this->assertEquals('12', $capturedMonth, 'Second optional parameter should be captured');
        $this->assertEmpty($capturedDay, 'Third optional parameter should be null');

        // Test with single parameter
        $this->router->dispatch('GET', 'archive/2023');
        $this->assertEquals('2023', $capturedYear, 'First optional parameter should be captured');
        $this->assertEmpty($capturedMonth, 'Second optional parameter should be null');
        $this->assertEmpty($capturedDay, 'Third optional parameter should be null');

        // Test without parameters
        $this->router->dispatch('GET', 'archive');
        $this->assertEmpty($capturedYear, 'First optional parameter should be null');
        $this->assertEmpty($capturedMonth, 'Second optional parameter should be null');
        $this->assertEmpty($capturedDay, 'Third optional parameter should be null');
    }
}