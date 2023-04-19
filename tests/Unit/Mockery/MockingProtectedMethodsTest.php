<?php
/**
 * Mockery
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://github.com/padraic/mockery/master/LICENSE
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to padraic@php.net so we can send you a copy immediately.
 *
 * @category   Mockery
 * @package    Mockery
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2010 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    http://github.com/padraic/mockery/blob/master/LICENSE New BSD License
 */

namespace MockeryTest\Unit\Mockery;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use MockeryTest\Fixture\TestIncreasedVisibilityChild;
use MockeryTest\Fixture\TestWithProtectedMethods;
use function mock;

class MockingProtectedMethodsTest extends MockeryTestCase
{
    /**
     * @test
     *
     * This is a regression test, basically we don't want the mock handling
     * interfering with calling protected methods partials
     */
    public function shouldAutomaticallyDeferCallsToProtectedMethodsForPartials()
    {
        $mock = mock(TestWithProtectedMethods::class.'[foo]');
        $this->assertEquals('bar', $mock->bar());
    }

    /**
     * @test
     *
     * This is a regression test, basically we don't want the mock handling
     * interfering with calling protected methods partials
     */
    public function shouldAutomaticallyDeferCallsToProtectedMethodsForRuntimePartials()
    {
        $mock = mock(TestWithProtectedMethods::class)->makePartial();
        $this->assertEquals('bar', $mock->bar());
    }

    /** @test */
    public function shouldAutomaticallyIgnoreAbstractProtectedMethods()
    {
        $mock = mock(TestWithProtectedMethods::class)->makePartial();
        $this->assertNull($mock->foo());
    }

    /** @test */
    public function shouldAllowMockingProtectedMethods()
    {
        $mock = mock(TestWithProtectedMethods::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $mock->shouldReceive('protectedBar')->andReturn('notbar');
        $this->assertEquals('notbar', $mock->bar());
    }

    /** @test */
    public function shouldAllowMockingProtectedMethodOnDefinitionTimePartial()
    {
        $mock = mock(TestWithProtectedMethods::class.'[protectedBar]')
            ->shouldAllowMockingProtectedMethods();

        $mock->shouldReceive('protectedBar')->andReturn('notbar');
        $this->assertEquals('notbar', $mock->bar());
    }

    /** @test */
    public function shouldAllowMockingAbstractProtectedMethods()
    {
        $mock = mock(TestWithProtectedMethods::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $mock->shouldReceive('abstractProtected')->andReturn('abstractProtected');
        $this->assertEquals('abstractProtected', $mock->foo());
    }

    /** @test */
    public function shouldAllowMockingIncreasedVisabilityMethods()
    {
        $mock = mock(TestIncreasedVisibilityChild::class);
        $mock->shouldReceive('foobar')->andReturn('foobar');
        $this->assertEquals('foobar', $mock->foobar());
    }
}
