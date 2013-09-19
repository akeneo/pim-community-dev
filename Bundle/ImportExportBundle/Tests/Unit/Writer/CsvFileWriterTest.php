<?php

namespace Oro\Bundle\ImportExportBundle\Tests\Unit\Writer;

use Oro\Bundle\BatchBundle\Entity\StepExecution;
use Oro\Bundle\ImportExportBundle\Writer\CsvFileWriter;

class CsvFileWriterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CsvFileWriter
     */
    protected $writer;

    /**
     * @var string
     */
    protected $filePath;

    protected function setUp()
    {
        $this->filePath = __DIR__ . '/fixtures/new_file.csv';
        $this->writer = new CsvFileWriter();
    }

    protected function tearDown()
    {
        if (is_file($this->filePath)) {
            unlink($this->filePath);
        }
    }

    /**
     * @expectedException \Oro\Bundle\ImportExportBundle\Exception\InvalidConfigurationException
     * @expectedExceptionMessage Configuration of CSV writer must contain "filePath".
     */
    public function testSetStepExecutionNoFileException()
    {
        $this->writer->setStepExecution($this->getMockStepExecution(array()));
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
            'filePath' => $this->filePath,
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
     * @dataProvider optionsDataProvider
     * @param array $options
     * @param array $data
     * @param string $expected
     */
    public function testWrite($options, $data, $expected)
    {
        $stepExecution = $this->getMockStepExecution($options);
        $this->writer->setStepExecution($stepExecution);
        $this->writer->write($data);
        $this->assertFileExists($expected);
        $this->assertFileEquals($expected, $options['filePath']);
    }

    public function optionsDataProvider()
    {
        $filePath = __DIR__ . '/fixtures/new_file.csv';
        return array(
            'first_item_header' => array(
                array('filePath' => $filePath),
                array(
                    array(
                        'field_one' => '1',
                        'field_two' => '2',
                        'field_three' => '3',
                    ),
                    array(
                        'field_one' => 'test1',
                        'field_two' => 'test2',
                        'field_three' => 'test3',
                    )
                ),
                __DIR__ . '/fixtures/first_item_header.csv'
            ),
            'defined_header' => array(
                array(
                    'filePath' => $filePath,
                    'header' => array('h1', 'h2', 'h3')
                ),
                array(
                    array(
                        'h1' => 'field_one',
                        'h2' => 'field_two',
                        'h3' => 'field_three'
                    )
                ),
                __DIR__ . '/fixtures/defined_header.csv'
            ),
            'no_header' => array(
                array(
                    'filePath' => $filePath,
                    'firstLineIsHeader' => false
                ),
                array(
                    array('1', '2', '3'),
                    array('test1', 'test2', 'test3')
                ),
                __DIR__ . '/fixtures/no_header.csv'
            )
        );
    }

    /**
     * @param array $jobInstanceRawConfiguration
     * @return \PHPUnit_Framework_MockObject_MockObject|StepExecution
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
