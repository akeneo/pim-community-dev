<?php

namespace Oro\Bundle\BatchBundle\Tests\Unit\Step;

use Oro\Bundle\BatchBundle\Step\ItemStep;
use Oro\Bundle\BatchBundle\Job\BatchStatus;
use Oro\Bundle\BatchBundle\Event\EventInterface;
use Oro\Bundle\BatchBundle\Item\InvalidItemException;

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
            'reader_foo' => 'bar',
            'processor_foo' => 'bar',
            'writer_foo' => 'bar',
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
        $config = array(
            'reader_foo' => 'reader_bar',
            'processor_foo' => 'processor_bar',
            'writer_foo' => 'writer_bar',
        );

        $reader->expects($this->once())
            ->method('setConfiguration')
            ->with($config);

        $processor->expects($this->once())
            ->method('setConfiguration')
            ->with($config);

        $writer->expects($this->once())
            ->method('setConfiguration')
            ->with($config);

        $this->itemStep->setConfiguration($config);
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
            ->will($this->onConsecutiveCalls(1, 2, 3, 4, 5, 6, 7));

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

    public function testDispatchReadInvalidItemException()
    {
        $stepExecution = $this->getMockBuilder('Oro\\Bundle\\BatchBundle\\Entity\\StepExecution')
            ->disableOriginalConstructor()
            ->getMock();
        $stepExecution->expects($this->any())
            ->method('getStatus')
            ->will($this->returnValue(new BatchStatus(BatchStatus::STARTING)));

        $this->eventDispatcher
            ->expects($this->at(1))
            ->method('dispatch')
            ->with(
                EventInterface::INVALID_ITEM,
                $this->logicalAnd(
                    $this->isInstanceOf('Oro\\Bundle\\BatchBundle\\Event\\InvalidItemEvent'),
                    $this->attributeEqualTo('reason', 'The read item is invalid'),
                    $this->attributeEqualTo('item', array('foo' => 'bar'))
                )
            );

        $reader = $this->getMock('Oro\\Bundle\\BatchBundle\\Tests\\Unit\\Item\\ItemReaderTestHelper');
        $reader->expects($this->exactly(2))
            ->method('read')
            ->will(
                $this->onConsecutiveCalls(
                    $this->throwException(new InvalidItemException('The read item is invalid', array('foo' => 'bar'))),
                    $this->returnValue(null)
                )
            );
        $reader->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('stub_reader'));

        $processor = $this->getMock('Oro\\Bundle\\BatchBundle\\Tests\\Unit\\Item\\ItemProcessorTestHelper');
        $processor->expects($this->never())
            ->method('process');

        $writer = $this->getMock('Oro\\Bundle\\BatchBundle\\Tests\\Unit\\Item\\ItemWriterTestHelper');
        $writer->expects($this->never())
            ->method('write');

        $this->itemStep->setReader($reader);
        $this->itemStep->setProcessor($processor);
        $this->itemStep->setWriter($writer);

        $stepExecution->expects($this->once())
            ->method('addWarning')
            ->with(
                'stub_reader',
                'The read item is invalid',
                array('foo' => 'bar')
            );

        $this->itemStep->setBatchSize(5);
        $this->itemStep->execute($stepExecution);
    }

    public function testDispatchProcessInvalidItemException()
    {
        $stepExecution = $this->getMockBuilder('Oro\\Bundle\\BatchBundle\\Entity\\StepExecution')
            ->disableOriginalConstructor()
            ->getMock();
        $stepExecution->expects($this->any())
            ->method('getStatus')
            ->will($this->returnValue(new BatchStatus(BatchStatus::STARTING)));

        $this->eventDispatcher
            ->expects($this->at(1))
            ->method('dispatch')
            ->with(
                EventInterface::INVALID_ITEM,
                $this->logicalAnd(
                    $this->isInstanceOf('Oro\\Bundle\\BatchBundle\\Event\\InvalidItemEvent'),
                    $this->attributeEqualTo('reason', 'The processed item is invalid'),
                    $this->attributeEqualTo('item', array('foo' => 'bar'))
                )
            );

        $reader = $this->getMock('Oro\\Bundle\\BatchBundle\\Tests\\Unit\\Item\\ItemReaderTestHelper');
        $reader->expects($this->exactly(2))
            ->method('read')
            ->will(
                $this->onConsecutiveCalls(
                    $this->returnValue(array('foo' => 'bar')),
                    $this->returnValue(null)
                )
            );

        $processor = $this->getMock('Oro\\Bundle\\BatchBundle\\Tests\\Unit\\Item\\ItemProcessorTestHelper');
        $processor->expects($this->exactly(1))
            ->method('process')
            ->will(
                $this->throwException(
                    new InvalidItemException('The processed item is invalid', array('foo' => 'bar'))
                )
            );
        $processor->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('stub_processor'));

        $writer = $this->getMock('Oro\\Bundle\\BatchBundle\\Tests\\Unit\\Item\\ItemWriterTestHelper');
        $writer->expects($this->never())
            ->method('write');

        $this->itemStep->setReader($reader);
        $this->itemStep->setProcessor($processor);
        $this->itemStep->setWriter($writer);

        $stepExecution->expects($this->once())
            ->method('addWarning')
            ->with(
                'stub_processor',
                'The processed item is invalid',
                array('foo' => 'bar')
            );

        $this->itemStep->setBatchSize(5);
        $this->itemStep->execute($stepExecution);
    }

    public function testDispatchWriteInvalidItemException()
    {
        $stepExecution = $this->getMockBuilder('Oro\\Bundle\\BatchBundle\\Entity\\StepExecution')
            ->disableOriginalConstructor()
            ->getMock();
        $stepExecution->expects($this->any())
            ->method('getStatus')
            ->will($this->returnValue(new BatchStatus(BatchStatus::STARTING)));

        $this->eventDispatcher
            ->expects($this->at(1))
            ->method('dispatch')
            ->with(
                EventInterface::INVALID_ITEM,
                $this->logicalAnd(
                    $this->isInstanceOf('Oro\\Bundle\\BatchBundle\\Event\\InvalidItemEvent'),
                    $this->attributeEqualTo('reason', 'The written item is invalid'),
                    $this->attributeEqualTo('item', array('foo' => 'bar'))
                )
            );

        $reader = $this->getMock('Oro\\Bundle\\BatchBundle\\Item\\ItemReaderInterface');
        $reader->expects($this->exactly(2))
            ->method('read')
            ->will(
                $this->onConsecutiveCalls(
                    $this->returnValue(array('foo' => 'bar')),
                    $this->returnValue(null)
                )
            );

        $processor = $this->getMock('Oro\\Bundle\\BatchBundle\\Tests\\Unit\\Item\\ItemProcessorTestHelper');
        $processor->expects($this->exactly(1))
            ->method('process')
            ->will(
                $this->returnValue(array('foo' => 'bar'))
            );

        $writer = $this->getMock('Oro\\Bundle\\BatchBundle\\Tests\\Unit\\Item\\ItemWriterTestHelper');
        $writer->expects($this->exactly(1))
            ->method('write')
            ->will(
                $this->throwException(
                    new InvalidItemException('The written item is invalid', array('foo' => 'bar'))
                )
            );
        $writer->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('stub_writer'));

        $this->itemStep->setReader($reader);
        $this->itemStep->setProcessor($processor);
        $this->itemStep->setWriter($writer);

        $stepExecution->expects($this->once())
            ->method('addWarning')
            ->with(
                'stub_writer',
                'The written item is invalid',
                array('foo' => 'bar')
            );

        $this->itemStep->setBatchSize(5);
        $this->itemStep->execute($stepExecution);
    }

    public function testProcessShouldNotReturnNull()
    {
        $stepExecution = $this->getMockBuilder('Oro\\Bundle\\BatchBundle\\Entity\\StepExecution')
            ->disableOriginalConstructor()
            ->getMock();
        $stepExecution->expects($this->any())
            ->method('getStatus')
            ->will($this->returnValue(new BatchStatus(BatchStatus::STARTING)));

        $stepExecution->expects($this->once())
            ->method('addFailureException');

        $this->eventDispatcher
            ->expects($this->at(1))
            ->method('dispatch')
            ->with(
                EventInterface::STEP_EXECUTION_ERRORED,
                $this->anything()
            );

        $reader = $this->getMock('Oro\\Bundle\\BatchBundle\\Tests\\Unit\\Item\\ItemReaderTestHelper');
        $reader->expects($this->any())
            ->method('read')
            ->will($this->returnValue(array('foo' => 'bar')));

        $processor = $this->getMock('Oro\\Bundle\\BatchBundle\\Tests\\Unit\\Item\\ItemProcessorTestHelper');
        $processor->expects($this->any())
            ->method('process')
            ->will($this->returnValue(null));

        $writer = $this->getMock('Oro\\Bundle\\BatchBundle\\Tests\\Unit\\Item\\ItemWriterTestHelper');

        $this->itemStep->setReader($reader);
        $this->itemStep->setProcessor($processor);
        $this->itemStep->setWriter($writer);

        $this->itemStep->execute($stepExecution);
    }

    /**
     * Assert the entity tested
     *
     * @param object $entity
     */
    protected function assertEntity($entity)
    {
        $this->assertInstanceOf('Oro\\Bundle\\BatchBundle\\Step\\ItemStep', $entity);
    }

    private function getReaderMock(array $configuration, array $fields = array())
    {
        $reader = $this
            ->getMockBuilder('Oro\\Bundle\\BatchBundle\\Tests\\Unit\\Item\\ItemReaderTestHelper')
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
            ->getMockBuilder('Oro\\Bundle\\BatchBundle\\Tests\\Unit\\Item\\ItemProcessorTestHelper')
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
            ->getMockBuilder('Oro\\Bundle\\BatchBundle\\Tests\\Unit\\Item\\ItemWriterTestHelper')
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
}
