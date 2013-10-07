<?php

namespace Oro\Bundle\ImportExportBundle\Tests\Unit\Processor;

use Oro\Bundle\ImportExportBundle\Processor\RegistryDelegateProcessor;

class RegistryDelegateProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $processorRegistry;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextRegistry;

    /**
     * @var string
     */
    protected $delegateType = 'import';

    /**
     * @var RegistryDelegateProcessor
     */
    protected $processor;

    protected function setUp()
    {
        $this->processorRegistry = $this->getMock('Oro\Bundle\ImportExportBundle\Processor\ProcessorRegistry');
        $this->contextRegistry = $this->getMock('Oro\Bundle\ImportExportBundle\Context\ContextRegistry');
        $this->processor = new RegistryDelegateProcessor(
            $this->processorRegistry,
            $this->delegateType,
            $this->contextRegistry
        );
    }

    public function testSetStepExecution()
    {
        $stepExecution = $this->getMockBuilder('Oro\Bundle\BatchBundle\Entity\StepExecution')
            ->disableOriginalConstructor()->getMock();
        $this->processor->setStepExecution($stepExecution);

        $this->assertAttributeEquals($stepExecution, 'stepExecution', $this->processor);
    }

    public function testProcessContextAwareProcessor()
    {
        $entityName = 'entity_name';
        $processorAlias = 'processor_alias';
        $stepExecution = $this->getMockStepExecution();
        $item = $this->getMock('MockItem');

        $delegateProcessor = $this->getMock('Oro\Bundle\ImportExportBundle\Processor\ContextAwareProcessor');

        $this->processorRegistry->expects($this->once())->method('getProcessor')
            ->with($this->delegateType, $processorAlias)
            ->will($this->returnValue($delegateProcessor));

        $context = $this->getMock('Oro\Bundle\ImportExportBundle\Context\ContextInterface');
        $this->contextRegistry->expects($this->once())->method('getByStepExecution')
            ->with($stepExecution)
            ->will($this->returnValue($context));

        $context->expects($this->any())
            ->method('getOption')
            ->will(
                $this->returnValueMap(
                    array(
                        array('entityName', null, $entityName),
                        array('processorAlias', null, $processorAlias),
                    )
                )
            );

        $delegateProcessor->expects($this->once())->method('setImportExportContext')->with($context);
        $delegateProcessor->expects($this->once())->method('process')->with($item);

        $this->processor->setStepExecution($stepExecution);
        $this->processor->process($item);
    }

    public function testProcessStepExecutionAwareProcessor()
    {
        $entityName = 'entity_name';
        $processorAlias = 'processor_alias';
        $stepExecution = $this->getMockStepExecution();
        $item = $this->getMock('MockItem');

        $delegateProcessor = $this->getMock('Oro\Bundle\ImportExportBundle\Processor\StepExecutionAwareProcessor');

        $context = $this->getMock('Oro\Bundle\ImportExportBundle\Context\ContextInterface');
        $this->contextRegistry->expects($this->once())->method('getByStepExecution')
            ->with($stepExecution)
            ->will($this->returnValue($context));

        $context->expects($this->any())
            ->method('getOption')
            ->will(
                $this->returnValueMap(
                    array(
                        array('entityName', null, $entityName),
                        array('processorAlias', null, $processorAlias),
                    )
                )
            );

        $this->processorRegistry->expects($this->once())->method('getProcessor')
            ->with($this->delegateType, $processorAlias)
            ->will($this->returnValue($delegateProcessor));

        $delegateProcessor->expects($this->once())->method('setStepExecution')->with($stepExecution);
        $delegateProcessor->expects($this->once())->method('process')->with($item);

        $this->processor->setStepExecution($stepExecution);
        $this->processor->process($item);
    }

    public function testProcessSimpleProcessor()
    {
        $entityName = 'entity_name';
        $processorAlias = 'processor_alias';
        $stepExecution = $this->getMockStepExecution();
        $item = $this->getMock('MockItem');

        $delegateProcessor = $this->getMock('Oro\Bundle\ImportExportBundle\Processor\ProcessorInterface');

        $context = $this->getMock('Oro\Bundle\ImportExportBundle\Context\ContextInterface');
        $this->contextRegistry->expects($this->once())->method('getByStepExecution')
            ->with($stepExecution)
            ->will($this->returnValue($context));

        $context->expects($this->any())
            ->method('getOption')
            ->will(
                $this->returnValueMap(
                    array(
                        array('entityName', null, $entityName),
                        array('processorAlias', null, $processorAlias),
                    )
                )
            );

        $this->processorRegistry->expects($this->once())->method('getProcessor')
            ->with($this->delegateType, $processorAlias)
            ->will($this->returnValue($delegateProcessor));

        $delegateProcessor->expects($this->never())->method('setImportExportContext');
        $delegateProcessor->expects($this->once())->method('process')->with($item);

        $this->processor->setStepExecution($stepExecution);
        $this->processor->process($item);
    }


    /**
     * @expectedException \Oro\Bundle\ImportExportBundle\Exception\InvalidConfigurationException
     * @expectedExceptionMessage Configuration of processor must contain "processorAlias" options.
     */
    public function testProcessFailsWhenNoConfigurationProvided()
    {
        $context = $this->getMock('Oro\Bundle\ImportExportBundle\Context\ContextInterface');

        $stepExecution = $this->getMockStepExecution(array());

        $this->contextRegistry->expects($this->once())->method('getByStepExecution')
            ->with($stepExecution)
            ->will($this->returnValue($context));

        $context->expects($this->any())
            ->method('getOption')
            ->will(
                $this->returnValueMap(
                    array(
                        array('entityName', null, null),
                        array('processorAlias', null, null),
                    )
                )
            );
        $this->processor->setStepExecution($stepExecution);
        $this->processor->process($this->getMock('MockItem'));
    }

    /**
     * @expectedException \Oro\Bundle\ImportExportBundle\Exception\LogicException
     * @expectedExceptionMessage Step execution entity must be injected to processor.
     */
    public function testProcessFailsWhenNoStepExecution()
    {
        $this->processor->process($this->getMock('MockItem'));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockStepExecution()
    {
        return $this->getMockBuilder('Oro\Bundle\BatchBundle\Entity\StepExecution')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
