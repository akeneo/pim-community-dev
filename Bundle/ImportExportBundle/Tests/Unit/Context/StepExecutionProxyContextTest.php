<?php

namespace Oro\Bundle\ImportExportBundle\Tests\Unit\Reader;

use Oro\Bundle\ImportExportBundle\Context\StepExecutionProxyContext;
use Oro\Bundle\ImportExportBundle\Exception\ErrorException;

class StepExecutionProxyContextTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $stepExecution;

    /**
     * @var StepExecutionProxyContext
     */
    protected $context;

    protected function setUp()
    {
        $this->stepExecution = $this->getMockBuilder('Oro\Bundle\BatchBundle\Entity\StepExecution')
            ->disableOriginalConstructor()
            ->getMock();
        $this->context = new StepExecutionProxyContext($this->stepExecution);
    }

    public function testAddError()
    {
        $message = 'Error message';

        $this->stepExecution->expects($this->once())
            ->method('addError')
            ->with($message);

        $this->context->addError($message);
    }

    public function testGetErrors()
    {
        $expected = array('Error message');

        $this->stepExecution->expects($this->once())
            ->method('getErrors')
            ->will($this->returnValue($expected));

        $this->assertEquals($expected, $this->context->getErrors());
    }

    public function testIncrementReadCount()
    {
        $this->stepExecution->expects($this->once())
            ->method('incrementReadCount');

        $this->context->incrementReadCount();
    }

    public function testGetReadCount()
    {
        $expected = 10;

        $this->stepExecution->expects($this->once())
            ->method('getReadCount')
            ->will($this->returnValue($expected));

        $this->assertEquals($expected, $this->context->getReadCount());
    }

    /**
     * @dataProvider incrementCountDataProvider
     */
    public function testIncrement($propertyName)
    {
        $expectedCount = 1;

        $executionContext = $this->getMock('Oro\Bundle\BatchBundle\Item\ExecutionContext');

        $this->stepExecution->expects($this->exactly(2))
            ->method('getExecutionContext')
            ->will($this->returnValue($executionContext));

        $executionContext->expects($this->at(0))
            ->method('get')
            ->with($propertyName)
            ->will($this->returnValue($expectedCount));

        $executionContext->expects($this->at(1))
            ->method('put')
            ->with($propertyName, $expectedCount + 1);

        $method = 'increment' . str_replace('_', '', $propertyName);
        $this->context->$method();
    }

    public function incrementCountDataProvider()
    {
        return array(
            array('read_offset'),
            array('update_count'),
            array('replace_count'),
            array('delete_count'),
            array('error_entries_count'),
            array('add_count')
        );
    }

    /**
     * @dataProvider getCountDataProvider
     */
    public function testGetCount($propertyName)
    {
        $expectedCount = 1;

        $executionContext = $this->getMock('Oro\Bundle\BatchBundle\Item\ExecutionContext');

        $this->stepExecution->expects($this->once())
            ->method('getExecutionContext')
            ->will($this->returnValue($executionContext));

        $executionContext->expects($this->once())
            ->method('get')
            ->with($propertyName)
            ->will($this->returnValue($expectedCount));

        $method = 'get' . str_replace('_', '', $propertyName);
        $this->assertEquals($expectedCount, $this->context->$method());
    }

    public function getCountDataProvider()
    {
        return array(
            array('read_offset'),
            array('update_count'),
            array('replace_count'),
            array('delete_count'),
            array('error_entries_count'),
            array('add_count')
        );
    }

    public function testGetConfiguration()
    {
        $expectedConfiguration = array('foo' => 'value');
        $this->expectGetRawConfiguration($expectedConfiguration);
        $this->assertSame($expectedConfiguration, $this->context->getConfiguration());
    }

    public function testHasConfigurationOption()
    {
        $expectedConfiguration = array('foo' => 'value');
        $this->expectGetRawConfiguration($expectedConfiguration, 2);
        $this->assertTrue($this->context->hasOption('foo'));
        $this->assertFalse($this->context->hasOption('unknown'));
    }

    public function testGetConfigurationOption()
    {
        $expectedConfiguration = array('foo' => 'value');
        $this->expectGetRawConfiguration($expectedConfiguration, 4);
        $this->assertEquals('value', $this->context->getOption('foo'));
        $this->assertEquals('default', $this->context->getOption('unknown', 'default'));
        $this->assertNull($this->context->getOption('unknown'));
    }

    protected function expectGetRawConfiguration(array $expectedConfiguration, $count = 1)
    {
        $jobInstance = $this->getMockBuilder('Oro\Bundle\BatchBundle\Entity\JobInstance')
            ->disableOriginalConstructor()
            ->getMock();

        $jobInstance->expects($this->exactly($count))->method('getRawConfiguration')
            ->will($this->returnValue($expectedConfiguration));

        $jobExecution = $this->getMockBuilder('Oro\Bundle\BatchBundle\Entity\JobExecution')
            ->disableOriginalConstructor()
            ->getMock();

        $jobExecution->expects($this->exactly($count))->method('getJobInstance')
            ->will($this->returnValue($jobInstance));

        $this->stepExecution->expects($this->exactly($count))->method('getJobExecution')
            ->will($this->returnValue($jobExecution));
    }

    public function testAddErrors()
    {
        $messages = array('Error 1', 'Error 2');

        $this->stepExecution->expects($this->exactly(2))
            ->method('addError');
        $this->stepExecution->expects($this->at(0))
            ->method('addError')
            ->with($messages[0]);
        $this->stepExecution->expects($this->at(1))
            ->method('addError')
            ->with($messages[1]);

        $this->context->addErrors($messages);
    }

    public function testGetFailureExceptions()
    {
        $exceptions = array(array('message' => 'Error 1'), array('message' => 'Error 2'));
        $expected = array('Error 1', 'Error 2');
        $this->stepExecution->expects($this->once())
            ->method('getFailureExceptions')
            ->will($this->returnValue($exceptions));
        $this->assertEquals($expected, $this->context->getFailureExceptions());
    }
}
