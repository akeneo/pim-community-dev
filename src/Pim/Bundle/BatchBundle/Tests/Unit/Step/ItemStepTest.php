<?php

namespace Pim\Bundle\BatchBundle\Tests\Unit\Job;

use Monolog\Logger;
use Monolog\Handler\TestHandler;
use Pim\Bundle\BatchBundle\Step\ItemStep;
use Pim\Bundle\BatchBundle\Job\BatchStatus;

/**
 * Tests related to the ItemStep class
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ItemStepTest extends \PHPUnit_Framework_TestCase
{
    protected $itemStep      = null;
    protected $logger        = null;
    protected $jobRepository = null;

    const STEP_NAME = 'test_step_name';

    protected function setUp()
    {
        $this->logger = new Logger('JobLogger');
        $this->logger->pushHandler(new TestHandler());

        $this->jobRepository = $this->getMock('Pim\\Bundle\\BatchBundle\\Job\\JobRepositoryInterface');

        $this->itemStep = new ItemStep(self::STEP_NAME); 

        $this->itemStep->setLogger($this->logger);
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
            ->getMockBuilder('Pim\Bundle\ImportExportBundle\Reader\ProductReader')
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
            ->getMockBuilder('Pim\Bundle\ImportExportBundle\Processor\CsvSerializerProcessor')
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
        $writer = $this->getMock('Pim\Bundle\ImportExportBundle\Writer\FileWriter');

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
        $reader = $this ->getMock('Pim\\Bundle\\BatchBundle\\Item\\ItemReaderInterface');

        $reader->expects($this->exactly(8))
            ->method('read')
            ->will($this->onConsecutiveCalls(1, 2, 3, 4, 5, 6, 7, null));

        $processor = $this ->getMock('Pim\\Bundle\\BatchBundle\\Item\\ItemProcessorInterface');

        $processor->expects($this->exactly(7))
            ->method('process')
            ->will($this->onConsecutiveCalls(1, null, 3, 4, 5, 6, 7,  null));

        $writer = $this ->getMock('Pim\\Bundle\\BatchBundle\\Item\\ItemWriterInterface');

        $writer->expects($this->exactly(2))
            ->method('write');

        $this->itemStep->setReader($reader);
        $this->itemStep->setProcessor($processor);
        $this->itemStep->setWriter($writer);

        $stepExecution = $this->getMockBuilder('Pim\\Bundle\\BatchBundle\\Entity\\StepExecution')
            ->disableOriginalConstructor()
            ->getMock();

        $stepExecution->expects($this->any())
            ->method('getStatus')
            ->will($this->returnValue(new BatchStatus(BatchStatus::STARTING)));

        $stepExecution->expects($this->once())
            ->method('setReadCount')
            ->with($this->equalTo(7));

        $stepExecution->expects($this->once())
            ->method('setWriteCount')
            ->with($this->equalTo(6));

        $stepExecution->expects($this->once())
            ->method('setFilterCount')
            ->with($this->equalTo(1));

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
        $this->assertInstanceOf('Pim\Bundle\BatchBundle\Step\ItemStep', $entity);
    }
}
