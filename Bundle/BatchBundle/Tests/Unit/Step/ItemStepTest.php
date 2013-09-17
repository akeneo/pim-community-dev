<?php

namespace Oro\Bundle\BatchBundle\Tests\Unit\Step;

use Oro\Bundle\BatchBundle\Step\ItemStep;
use Oro\Bundle\BatchBundle\Job\BatchStatus;
use Oro\Bundle\BatchBundle\Event\EventInterface;

/**
 * Tests related to the ItemStep class
 *
 */
class ItemStepTest extends \PHPUnit_Framework_TestCase
{
    const STEP_NAME = 'test_step_name';

    /**
     * @var ItemStep
     */
    protected $itemStep = null;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventDispatcher = null;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $jobRepository = null;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->eventDispatcher = $this->getMock('Symfony\\Component\\EventDispatcher\\EventDispatcherInterface');
        $this->jobRepository   = $this->getMock('Oro\\Bundle\\BatchBundle\\Job\\JobRepositoryInterface');

        $this->itemStep = new ItemStep(self::STEP_NAME);

        $this->itemStep->setEventDispatcher($this->eventDispatcher);
        $this->itemStep->setJobRepository($this->jobRepository);
    }

    public function testGetConfiguration()
    {
        $reader    = $this->getReaderMock(array('reader_foo' => 'bar'), array('reader_foo'));
        $processor = $this->getProcessorMock(array('processor_foo' => 'bar'), array('processor_foo'));
        $writer    = $this->getWriterMock(array('writer_foo' => 'bar'), array('writer_foo'));

        $this->itemStep->setReader($reader);
        $this->itemStep->setProcessor($processor);
        $this->itemStep->setWriter($writer);

        $expectedConfiguration = array(
            'reader' => array(
                'reader_foo' => 'bar'
            ),
            'processor' => array(
                'processor_foo' => 'bar'
            ),
            'writer' => array(
                'writer_foo' => 'bar'
            )
        );

        $this->assertEquals($expectedConfiguration, $this->itemStep->getConfiguration());
    }

    public function testSetConfiguration()
    {
        $reader    = $this->getReaderMock(array(), array('reader_foo'));
        $processor = $this->getProcessorMock(array(), array('processor_foo'));
        $writer    = $this->getWriterMock(array(), array('writer_foo'));

        $this->itemStep->setReader($reader);
        $this->itemStep->setProcessor($processor);
        $this->itemStep->setWriter($writer);

        $reader->expects($this->once())
            ->method('setConfiguration')
            ->with(array('reader_foo' => 'reader_bar'));

        $processor->expects($this->once())
            ->method('setConfiguration')
            ->with(array('processor_foo' => 'processor_bar'));

        $writer->expects($this->once())
            ->method('setConfiguration')
            ->with(array('writer_foo' => 'writer_bar'));

        $this->itemStep->setConfiguration(
            array(
                'reader' => array(
                    'reader_foo' => 'reader_bar',
                ),
                'processor' => array(
                    'processor_foo' => 'processor_bar',
                ),
                'writer' => array(
                    'writer_foo' => 'writer_bar',
                )
            )
        );
    }

    private function getReaderMock(array $configuration, array $fields = array())
    {
        $reader = $this
            ->getMockBuilder('Oro\Bundle\BatchBundle\Tests\Unit\Item\ItemReaderTestHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $reader->expects($this->any())
            ->method('getConfiguration')
            ->will($this->returnValue($configuration));

        $reader->expects($this->any())
            ->method('getConfigurationFields')
            ->will($this->returnValue($fields));

        return $reader;
    }

    private function getProcessorMock(array $configuration, array $fields = array())
    {
        $processor = $this
            ->getMockBuilder('Oro\Bundle\BatchBundle\Tests\Unit\Item\ItemProcessorTestHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $processor->expects($this->any())
            ->method('getConfiguration')
            ->will($this->returnValue($configuration));

        $processor->expects($this->any())
            ->method('getConfigurationFields')
            ->will($this->returnValue($fields));

        return $processor;
    }

    private function getWriterMock(array $configuration, array $fields = array())
    {
        $writer = $this
            ->getMockBuilder('Oro\Bundle\BatchBundle\Tests\Unit\Item\ItemWriterTestHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $writer->expects($this->any())
            ->method('getConfiguration')
            ->will($this->returnValue($configuration));

        $writer->expects($this->any())
            ->method('getConfigurationFields')
            ->will($this->returnValue($fields));

        return $writer;
    }

    public function testExecute()
    {
        $stepExecution = $this->getMockBuilder('Oro\\Bundle\\BatchBundle\\Entity\\StepExecution')
            ->disableOriginalConstructor()
            ->getMock();
        $stepExecution->expects($this->any())
            ->method('getStatus')
            ->will($this->returnValue(new BatchStatus(BatchStatus::STARTING)));

        $reader = $this->getMockBuilder('Oro\\Bundle\\BatchBundle\\Tests\\Unit\\Step\\Stub\\ReaderStub')
            ->setMethods(array('setStepExecution', 'read'))
            ->getMock();
        $reader->expects($this->once())
            ->method('setStepExecution')
            ->with($stepExecution);
        $reader->expects($this->exactly(8))
            ->method('read')
            ->will($this->onConsecutiveCalls(1, 2, 3, 4, 5, 6, 7, null));

        $processor = $this->getMockBuilder('Oro\\Bundle\\BatchBundle\\Tests\\Unit\\Step\\Stub\\ProcessorStub')
            ->setMethods(array('setStepExecution', 'process'))
            ->getMock();
        $processor->expects($this->once())
            ->method('setStepExecution')
            ->with($stepExecution);
        $processor->expects($this->exactly(7))
            ->method('process')
            ->will($this->onConsecutiveCalls(1, null, 3, 4, 5, 6, 7, null));

        $writer = $this->getMockBuilder('Oro\\Bundle\\BatchBundle\\Tests\\Unit\\Step\\Stub\\WriterStub')
            ->setMethods(array('setStepExecution', 'write'))
            ->getMock();
        $writer->expects($this->once())
            ->method('setStepExecution')
            ->with($stepExecution);
        $writer->expects($this->exactly(2))
            ->method('write');

        $this->itemStep->setReader($reader);
        $this->itemStep->setProcessor($processor);
        $this->itemStep->setWriter($writer);
        $this->itemStep->setBatchSize(5);
        $this->itemStep->execute($stepExecution);
    }

    public function testDispatchReaderWarning()
    {
        $reader = $this ->getMock('Oro\\Bundle\\BatchBundle\\Item\\ItemReaderInterface');
        $reader->expects($this->exactly(2))
            ->method('read')
            ->will($this->onConsecutiveCalls(false, null));
        $this->eventDispatcher
            ->expects($this->at(1))
            ->method('dispatch')
            ->with(
                EventInterface::INVALID_READER_EXECUTION,
                $this->isInstanceOf('Oro\Bundle\BatchBundle\Event\StepExecutionEvent')
            );

        $processor = $this ->getMock('Oro\\Bundle\\BatchBundle\\Item\\ItemProcessorInterface');
        $processor->expects($this->never())
            ->method('process');

        $writer = $this ->getMock('Oro\\Bundle\\BatchBundle\\Item\\ItemWriterInterface');
        $writer->expects($this->never())
            ->method('write');

        $this->itemStep->setReader($reader);
        $this->itemStep->setProcessor($processor);
        $this->itemStep->setWriter($writer);

        $stepExecution = $this->getMockBuilder('Oro\\Bundle\\BatchBundle\\Entity\\StepExecution')
            ->disableOriginalConstructor()
            ->getMock();

        $stepExecution->expects($this->any())
            ->method('getStatus')
            ->will($this->returnValue(new BatchStatus(BatchStatus::STARTING)));

        $this->itemStep->setBatchSize(5);
        $this->itemStep->execute($stepExecution);
    }

    /**
     * Assert the entity tested
     *
     * @param object $entity
     */
    protected function assertEntity($entity)
    {
        $this->assertInstanceOf('Oro\Bundle\BatchBundle\Step\ItemStep', $entity);
    }
}
