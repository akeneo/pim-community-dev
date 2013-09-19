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
        $expected = array('message');

        $this->stepExecution->expects($this->once())
            ->method('getFailureExceptionMessages')
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
    public function testIncrementCount($countName)
    {
        $expectedCount = 1;

        $expectedPropertyName = $countName . '_count';

        $executionContext = $this->getMock('Oro\Bundle\BatchBundle\Item\ExecutionContext');

        $this->stepExecution->expects($this->exactly(2))
            ->method('getExecutionContext')
            ->will($this->returnValue($executionContext));

        $executionContext->expects($this->at(0))
            ->method('get')
            ->with($expectedPropertyName)
            ->will($this->returnValue($expectedCount));

        $executionContext->expects($this->at(1))
            ->method('put')
            ->with($expectedPropertyName, $expectedCount + 1);

        $method = 'increment' . ucfirst($countName) . 'Count';
        $this->context->$method();
    }

    public function incrementCountDataProvider()
    {
        return array(
            array('update'),
            array('replace'),
            array('delete'),
        );
    }

    /**
     * @dataProvider getCountDataProvider
     */
    public function testGetCount($countName)
    {
        $expectedCount = 1;

        $expectedPropertyName = $countName . '_count';

        $executionContext = $this->getMock('Oro\Bundle\BatchBundle\Item\ExecutionContext');

        $this->stepExecution->expects($this->once())
            ->method('getExecutionContext')
            ->will($this->returnValue($executionContext));

        $executionContext->expects($this->once())
            ->method('get')
            ->with($expectedPropertyName)
            ->will($this->returnValue($expectedCount));

        $method = 'get' . ucfirst($countName) . 'Count';
        $this->assertEquals($expectedCount, $this->context->$method($countName));
    }

    public function getCountDataProvider()
    {
        return array(
            array('update'),
            array('replace'),
            array('delete'),
        );
    }

    public function testGetConfiguration()
    {
        $expectedConfiguration = array('name' => 'value');

        $jobInstance = $this->getMockBuilder('Oro\Bundle\BatchBundle\Entity\JobInstance')
            ->disableOriginalConstructor()
            ->getMock();

        $jobInstance->expects($this->once())->method('getRawConfiguration')
            ->will($this->returnValue($expectedConfiguration));

        $jobExecution = $this->getMockBuilder('Oro\Bundle\BatchBundle\Entity\JobExecution')
            ->disableOriginalConstructor()
            ->getMock();

        $jobExecution->expects($this->once())->method('getJobInstance')
            ->will($this->returnValue($jobInstance));

        $this->stepExecution->expects($this->once())->method('getJobExecution')
            ->will($this->returnValue($jobExecution));

        $this->assertSame($expectedConfiguration, $this->context->getConfiguration());
    }
}
