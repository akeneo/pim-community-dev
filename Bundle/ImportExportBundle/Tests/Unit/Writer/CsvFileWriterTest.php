<?php

namespace Oro\Bundle\ImportExportBundle\Tests\Unit\Writer;

use Oro\Bundle\ImportExportBundle\Writer\CsvFileWriter;

class CsvFileWriterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CsvFileWriter
     */
    protected $writer;

    protected function setUp()
    {
        $this->writer = new CsvFileWriter();
    }

    /**
     * @expectedException \Oro\Bundle\ImportExportBundle\Exception\InvalidArgumentException
     */
    public function testUnknownFileException()
    {
        $this->writer->setStepExecution(
            $this->getMockStepExecution(
                array(
                    'filePath' =>  __DIR__ . '/unknown/new_file.csv'
                )
            )
        );
    }

    public function testSetStepExecution()
    {
        $options = array(
            'filePath' => __DIR__ . '/fixtures/new_file.csv',
            'delimiter' => ',',
            'enclosure' => "'''",
            'firstLineIsHeader' => false,
            'header' => array('one', 'two')
        );

        $this->assertAttributeEquals(';', 'delimiter', $this->writer);
        $this->assertAttributeEquals('"', 'enclosure', $this->writer);
        $this->assertAttributeEquals(true, 'firstLineIsHeader', $this->writer);
        $this->assertAttributeEmpty('header', $this->writer);

        $this->writer->setStepExecution($this->getMockStepExecution($options));

        $this->assertAttributeEquals($options['delimiter'], 'delimiter', $this->writer);
        $this->assertAttributeEquals($options['enclosure'], 'enclosure', $this->writer);
        $this->assertAttributeEquals($options['firstLineIsHeader'], 'firstLineIsHeader', $this->writer);
        $this->assertAttributeEquals($options['header'], 'header', $this->writer);
    }

    /**
     * @param array $jobInstanceRawConfiguration
     * @return \PHPUnit_Framework_MockObject_MockObject+
     */
    protected function getMockStepExecution(array $jobInstanceRawConfiguration)
    {
        $jobInstance = $this->getMockBuilder('Oro\Bundle\BatchBundle\Entity\JobInstance')
            ->disableOriginalConstructor()
            ->getMock();

        $jobInstance->expects($this->once())->method('getRawConfiguration')
            ->will($this->returnValue($jobInstanceRawConfiguration));

        $jobExecution = $this->getMockBuilder('Oro\Bundle\BatchBundle\Entity\JobExecution')
            ->disableOriginalConstructor()
            ->getMock();

        $jobExecution->expects($this->once())->method('getJobInstance')
            ->will($this->returnValue($jobInstance));

        $stepExecution = $this->getMockBuilder('Oro\Bundle\BatchBundle\Entity\StepExecution')
            ->disableOriginalConstructor()
            ->getMock();
        $stepExecution->expects($this->once())->method('getJobExecution')
            ->will($this->returnValue($jobExecution));

        return $stepExecution;
    }
}
