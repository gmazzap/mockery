<?php

namespace MockeryTest\Unit\Mockery;

use BadMethodCallException;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\Exception;
use Mockery\Mock;
use Mockery\MockInterface;
use MockeryTest\Fixture\ClassWithMethods;
use MockeryTest\Fixture\ClassWithNoToString;
use MockeryTest\Fixture\ClassWithProtectedMethod;
use MockeryTest\Fixture\ClassWithToString;
use MockeryTest\Fixture\ExampleClassForTestingNonExistentMethod;
use function method_exists;
use function mock;

class Mockery_MockTest extends MockeryTestCase
{
    public function testAnonymousMockWorksWithNotAllowingMockingOfNonExistentMethods()
    {
        Mockery::getConfiguration()->allowMockingNonExistentMethods(false);
        $m = mock();
        $m->shouldReceive('test123')->andReturn(true);
        self::assertTrue($m->test123());
        Mockery::getConfiguration()->allowMockingNonExistentMethods(true);
    }
    public function testMockWithNotAllowingMockingOfNonExistentMethodsCanBeGivenAdditionalMethodsToMockEvenIfTheyDontExistOnClass()
    {
        Mockery::getConfiguration()->allowMockingNonExistentMethods(false);
        $m = mock(ExampleClassForTestingNonExistentMethod::class);
        $m->shouldAllowMockingMethod('testSomeNonExistentMethod');
        $m->shouldReceive('testSomeNonExistentMethod')->andReturn(true)->once();
        self::assertTrue($m->testSomeNonExistentMethod());
        Mockery::getConfiguration()->allowMockingNonExistentMethods(true);
    }
    public function testProtectedMethodMockWithNotAllowingMockingOfNonExistentMethodsWhenShouldAllowMockingProtectedMethodsIsCalled()
    {
        Mockery::getConfiguration()->allowMockingNonExistentMethods(false);
        $m = mock(ClassWithProtectedMethod::class);
        $m->shouldAllowMockingProtectedMethods();
        $m->shouldReceive('foo')->andReturn(true);
        self::assertTrue($m->foo());
        Mockery::getConfiguration()->allowMockingNonExistentMethods(true);
    }
    public function testShouldAllowMockingMethodReturnsMockInstance()
    {
        $m = Mockery::mock('someClass');
        $this->assertInstanceOf(MockInterface::class, $m->shouldAllowMockingMethod('testFunction'));
    }
    public function testShouldAllowMockingProtectedMethodReturnsMockInstance()
    {
        $m = Mockery::mock('someClass');
        $this->assertInstanceOf(MockInterface::class, $m->shouldAllowMockingProtectedMethods('testFunction'));
    }
    public function testMockAddsToString()
    {
        $mock = mock(ClassWithNoToString::class);
        $this->assertTrue(method_exists($mock, '__toString'));
    }
    public function testMockToStringMayBeDeferred()
    {
        $mock = mock(ClassWithToString::class)->makePartial();
        $this->assertEquals('foo', (string) $mock);
    }
    public function testMockToStringShouldIgnoreMissingAlwaysReturnsString()
    {
        $mock = mock(ClassWithNoToString::class)->shouldIgnoreMissing();
        $this->assertNotEquals('', (string) $mock);
        $mock->asUndefined();
        $this->assertNotEquals('', (string) $mock);
    }
    public function testShouldIgnoreMissing()
    {
        $mock = mock(ClassWithNoToString::class)->shouldIgnoreMissing();
        $this->assertNull($mock->nonExistingMethod());
    }
    public function testShouldIgnoreMissingDisallowMockingNonExistentMethodsUsingGlobalConfiguration()
    {
        Mockery::getConfiguration()->allowMockingNonExistentMethods(false);
        $mock = mock(ClassWithMethods::class)->shouldIgnoreMissing();
        $this->expectException(Exception::class);
        $mock->shouldReceive('nonExistentMethod');
    }
    public function testShouldIgnoreMissingCallingNonExistentMethodsUsingGlobalConfiguration()
    {
        Mockery::getConfiguration()->allowMockingNonExistentMethods(false);
        $mock = mock(ClassWithMethods::class)->shouldIgnoreMissing();
        $this->expectException(BadMethodCallException::class);
        $mock->nonExistentMethod();
    }
    public function testShouldIgnoreMissingCallingExistentMethods()
    {
        Mockery::getConfiguration()->allowMockingNonExistentMethods(false);
        $mock = mock(ClassWithMethods::class)->shouldIgnoreMissing();
        self::assertNull($mock->foo());
        $mock->shouldReceive('bar')->passthru();
        self::assertSame('bar', $mock->bar());
    }
    public function testShouldIgnoreMissingCallingNonExistentMethods()
    {
        Mockery::getConfiguration()->allowMockingNonExistentMethods(true);
        $mock = mock(ClassWithMethods::class)->shouldIgnoreMissing();
        self::assertNull($mock->foo());
        self::assertNull($mock->bar());
        self::assertNull($mock->nonExistentMethod());
        $mock->shouldReceive(array('foo' => 'new_foo', 'nonExistentMethod' => 'result'));
        $mock->shouldReceive('bar')->passthru();
        self::assertSame('new_foo', $mock->foo());
        self::assertSame('bar', $mock->bar());
        self::assertSame('result', $mock->nonExistentMethod());
    }
    public function testCanMockException()
    {
        $exception = Mockery::mock('Exception');
        $this->assertInstanceOf('Exception', $exception);
    }
    public function testCanMockSubclassOfException()
    {
        $errorException = Mockery::mock('ErrorException');
        $this->assertInstanceOf('ErrorException', $errorException);
        $this->assertInstanceOf('Exception', $errorException);
    }
    public function testCallingShouldReceiveWithoutAValidMethodName()
    {
        $mock = Mockery::mock();
        $this->expectException('InvalidArgumentException', 'Received empty method name');
        $mock->shouldReceive('');
    }
    public function testShouldThrowExceptionWithInvalidClassName()
    {
        $this->expectException(Exception::class);
        mock('ClassName.CannotContainDot');
    }
    /** @test */
    public function expectation_count_will_count_expectations()
    {
        $mock = new Mock();
        $mock->shouldReceive('doThis')->once();
        $mock->shouldReceive('doThat')->once();
        $this->assertEquals(2, $mock->mockery_getExpectationCount());
    }
    /** @test */
    public function expectation_count_will_ignore_defaults_if_overriden()
    {
        $mock = new Mock();
        $mock->shouldReceive('doThis')->once()->byDefault();
        $mock->shouldReceive('doThis')->twice();
        $mock->shouldReceive('andThis')->twice();
        $this->assertEquals(2, $mock->mockery_getExpectationCount());
    }
    /** @test */
    public function expectation_count_will_count_defaults_if_not_overriden()
    {
        $mock = new Mock();
        $mock->shouldReceive('doThis')->once()->byDefault();
        $mock->shouldReceive('doThat')->once()->byDefault();
        $this->assertEquals(2, $mock->mockery_getExpectationCount());
    }
}