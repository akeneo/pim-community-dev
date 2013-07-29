<?php

namespace Pim\Bundle\BatchBundle\Tests\Unit\Job;

use Pim\Bundle\BatchBundle\Job\SimpleJob;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SimpleJobTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $logger = $this
            ->getMockBuilder('Monolog\Logger')
            ->disableOriginalConstructor()
            ->getMock();
        $this->job = new SimpleJob('job_test', $logger);
    }

    public function testGetConfiguration()
    {
        $reader    = $this->getReaderMock(array('reader_foo' => 'bar'));
        $processor = $this->getProcessorMock(array('processor_foo' => 'bar'));
        $writer    = $this->getWriterMock(array('writer_foo' => 'bar'));
        $step      = $this->getStepMock('export', $reader, $processor, $writer);

        $this->job->addStep($step);
        $expectedConfiguration = array(
            'export' => array(
                'reader' => array(
                    'reader_foo' => 'bar'
                ),
                'processor' => array(
                    'processor_foo' => 'bar'
                ),
                'writer' => array(
                    'writer_foo' => 'bar'
                )
            )
        );

        $this->assertEquals($expectedConfiguration, $this->job->getConfiguration());
    }

    public function testSetConfiguration()
    {
        $reader    = $this->getReaderMock(array(), array('reader_foo'));
        $processor = $this->getProcessorMock(array(), array('processor_foo'));
        $writer    = $this->getWriterMock(array(), array('writer_foo'));
        $step      = $this->getStepMock('export', $reader, $processor, $writer);

        $reader->expects($this->once())
            ->method('setConfiguration')
            ->with(array('reader_foo' => 'reader_bar'));

        $processor->expects($this->once())
            ->method('setConfiguration')
            ->with(array('processor_foo' => 'processor_bar'));

        $writer->expects($this->once())
            ->method('setConfiguration')
            ->with(array('writer_foo' => 'writer_bar'));

        $this->job->addStep($step);
        $this->job->setConfiguration(array(
            'export' => array(
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
        ));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testSetConfigurationWithDifferentSteps()
    {
        $reader    = $this->getReaderMock(array(), array('reader_foo'));
        $processor = $this->getProcessorMock(array(), array('processor_foo'));
        $writer    = $this->getWriterMock(array(), array('writer_foo'));
        $step      = $this->getStepMock('export', $reader, $processor, $writer);

        $this->job->addStep($step);
        $this->job->setConfiguration(array(
            'unknown' => array(),
            'export' => array(
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
        ));
    }

    public function getStepMock($name, $reader, $processor, $writer)
    {
        $step = $this
            ->getMockBuilder('Pim\Bundle\BatchBundle\Step\ItemStep')
            ->disableOriginalConstructor()
            ->getMock();

        $step->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($name));

        $step->expects($this->any())
            ->method('getReader')
            ->will($this->returnValue($reader));

        $step->expects($this->any())
            ->method('getProcessor')
            ->will($this->returnValue($processor));

        $step->expects($this->any())
            ->method('getWriter')
            ->will($this->returnValue($writer));

        return $step;
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
}
