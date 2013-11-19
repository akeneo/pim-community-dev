<?php

namespace Oro\Bundle\ImportExportBundle\Tests\Unit\Reader;

use Oro\Bundle\BatchBundle\Entity\StepExecution;
use Oro\Bundle\ImportExportBundle\Reader\CsvFileReader;

class CsvFileReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CsvFileReader
     */
    protected $reader;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextRegistry;

    protected function setUp()
    {
        $this->contextRegistry = $this->getMockBuilder('Oro\Bundle\ImportExportBundle\Context\ContextRegistry')
            ->disableOriginalConstructor()
            ->setMethods(array('getByStepExecution'))
            ->getMock();

        $this->reader = new CsvFileReader($this->contextRegistry);
    }

    /**
     * @expectedException \Oro\Bundle\ImportExportBundle\Exception\InvalidConfigurationException
     * @expectedExceptionMessage Configuration of CSV reader must contain "filePath".
     */
    public function testSetStepExecutionNoFileException()
    {
        $context = $this->getContextWithOptionsMock(array());
        $this->reader->setStepExecution($this->getMockStepExecution($context));
    }

    /**
     * @expectedException \Oro\Bundle\ImportExportBundle\Exception\InvalidArgumentException
     * @expectedExceptionMessage File "unknown_file.csv" does not exists.
     */
    public function testUnknownFileException()
    {
        $context = $this->getContextWithOptionsMock(array('filePath' => 'unknown_file.csv'));
        $this->reader->setStepExecution($this->getMockStepExecution($context));
    }

    public function testSetStepExecution()
    {
        $options = array(
            'filePath' => __DIR__ . '/fixtures/import_correct.csv',
            'delimiter' => ',',
            'enclosure' => "'''",
            'escape' => ';',
            'firstLineIsHeader' => false,
            'header' => array('one', 'two')
        );

        $this->assertAttributeEquals(',', 'delimiter', $this->reader);
        $this->assertAttributeEquals('"', 'enclosure', $this->reader);
        $this->assertAttributeEquals('\\', 'escape', $this->reader);
        $this->assertAttributeEquals(true, 'firstLineIsHeader', $this->reader);
        $this->assertAttributeEmpty('header', $this->reader);

        $context = $this->getContextWithOptionsMock($options);
        $this->reader->setStepExecution($this->getMockStepExecution($context));

        $this->assertAttributeEquals($options['delimiter'], 'delimiter', $this->reader);
        $this->assertAttributeEquals($options['enclosure'], 'enclosure', $this->reader);
        $this->assertAttributeEquals($options['escape'], 'escape', $this->reader);
        $this->assertAttributeEquals($options['firstLineIsHeader'], 'firstLineIsHeader', $this->reader);
        $this->assertAttributeEquals($options['header'], 'header', $this->reader);
    }

    /**
     * @dataProvider optionsDataProvider
     * @param array $options
     * @param array $expected
     */
    public function testRead($options, $expected)
    {
        $context = $this->getContextWithOptionsMock($options);
        $stepExecution = $this->getMockStepExecution($context);
        $this->reader->setStepExecution($stepExecution);
        $context->expects($this->atLeastOnce())
            ->method('incrementReadOffset');
        $context->expects($this->atLeastOnce())
            ->method('incrementReadCount');
        $stepExecution->expects($this->never())
            ->method('addReaderWarning');
        $data = array();
        while (($dataRow = $this->reader->read($stepExecution)) !== null) {
            $data[] = $dataRow;
        }
        $this->assertEquals($expected, $data);
    }

    public function optionsDataProvider()
    {
        return array(
            array(
                array('filePath' => __DIR__ . '/fixtures/import_correct.csv'),
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
                    ),
                    array(),
                    array(
                        'field_one' => 'after_new1',
                        'field_two' => 'after_new2',
                        'field_three' => 'after_new3',
                    ),
                )
            ),
            array(
                array(
                    'filePath' => __DIR__ . '/fixtures/import_correct.csv',
                    'header' => array('h1', 'h2', 'h3')
                ),
                array(
                    array(
                        'h1' => 'field_one',
                        'h2' => 'field_two',
                        'h3' => 'field_three'
                    ),
                    array(
                        'h1' => '1',
                        'h2' => '2',
                        'h3' => '3',
                    ),
                    array(
                        'h1' => 'test1',
                        'h2' => 'test2',
                        'h3' => 'test3',
                    ),
                    array(),
                    array(
                        'h1' => 'after_new1',
                        'h2' => 'after_new2',
                        'h3' => 'after_new3',
                    ),
                )
            ),
            array(
                array(
                    'filePath' => __DIR__ . '/fixtures/import_correct.csv',
                    'firstLineIsHeader' => false
                ),
                array(
                    array('field_one', 'field_two', 'field_three'),
                    array('1', '2', '3'),
                    array('test1', 'test2', 'test3'),
                    array(),
                    array('after_new1', 'after_new2', 'after_new3'),
                )
            )
        );
    }

    /**
     * @expectedException Oro\Bundle\BatchBundle\Item\InvalidItemException
     * @expectedExceptionMessage Expecting to get 3 columns, actually got 2
     */
    public function testReadError()
    {
        $context = $this->getContextWithOptionsMock(array('filePath' => __DIR__ . '/fixtures/import_incorrect.csv'));
        $stepExecution = $this->getMockStepExecution($context);
        $this->reader->setStepExecution($stepExecution);
        $this->reader->read($stepExecution);
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $context
     * @return \PHPUnit_Framework_MockObject_MockObject|StepExecution
     */
    protected function getMockStepExecution($context)
    {
        $stepExecution = $this->getMockBuilder('Oro\Bundle\BatchBundle\Entity\StepExecution')
            ->disableOriginalConstructor()
            ->getMock();

        $this->contextRegistry->expects($this->any())
            ->method('getByStepExecution')
            ->with($stepExecution)
            ->will($this->returnValue($context));

        return $stepExecution;
    }

    protected function getContextWithOptionsMock($options)
    {
        $context = $this->getMockBuilder('Oro\Bundle\ImportExportBundle\Context\StepExecutionProxyContext')
            ->disableOriginalConstructor()
            ->getMock();
        $context->expects($this->any())
            ->method('hasOption')
            ->will(
                $this->returnCallback(
                    function ($option) use ($options) {
                        return isset($options[$option]);
                    }
                )
            );
        $context->expects($this->any())
            ->method('getOption')
            ->will(
                $this->returnCallback(
                    function ($option) use ($options) {
                        return $options[$option];
                    }
                )
            );
        return $context;
    }
}
