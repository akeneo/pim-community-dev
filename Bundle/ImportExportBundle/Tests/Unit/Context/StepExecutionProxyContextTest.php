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

    /**
     * @dataProvider addErrorDataProvider
     *
     * @param string $message
     * @param int $severity
     * @param \Exception $expectedException
     */
    public function testAddError($message, $severity, \Exception $expectedException)
    {
        $this->stepExecution->expects($this->once())
            ->method('addFailureException')
            ->with($expectedException);

        $this->context->addError($message, $severity);
    }

    public function addErrorDataProvider()
    {
        return array(
            array(
                'message',
                null,
                new ErrorException('message', 0, ErrorException::CRITICAL)
            ),
            array(
                'message',
                ErrorException::WARNING,
                new ErrorException('message', 0, ErrorException::WARNING)
            ),
        );
    }

    public function testGetErrors()
    {
        $exceptions = array(array('message' => 'testException'));
        $expected = array('testException');

        $this->stepExecution->expects($this->once())
            ->method('getFailureExceptions')
            ->will($this->returnValue($exceptions));

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
}
