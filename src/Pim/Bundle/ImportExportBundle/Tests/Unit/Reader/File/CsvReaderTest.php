<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Reader\File;

use Pim\Bundle\ImportExportBundle\Reader\File\CsvReader;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CsvReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test related method
     */
    protected function setUp()
    {
        $this->archiver = $this->getArchiverMock();
        $this->reader = new CsvReader($this->archiver);
        $this->stepExecution = $this->getStepExecutionMock();
        $this->reader->setStepExecution($this->stepExecution);

    }

    /**
     * Test related method
     */
    public function testDefaultValues()
    {
        $this->assertEquals(null, $this->reader->getFilePath());
        $this->assertEquals(';', $this->reader->getDelimiter());
        $this->assertEquals('"', $this->reader->getEnclosure());
        $this->assertEquals('\\', $this->reader->getEscape());
        $this->assertEquals(false, $this->reader->isUploadAllowed());
    }

    /**
     * Test related method
     */
    public function testRead()
    {
        $this->reader->setFilePath(__DIR__ . '/../../../fixtures/import.csv');

        $this->archiver
            ->expects($this->once())
            ->method('setHeader')
            ->with(array('firstname', 'lastname', 'age'));

        $this->stepExecution
            ->expects($this->exactly(3))
            ->method('incrementSummaryInfo');

        $this->assertEquals(
            array('firstname' => 'Severin', 'lastname' => 'Gero', 'age' => '28'),
            $this->reader->read()
        );
        $this->assertEquals(
            array('firstname' => 'Kyrylo', 'lastname' => 'Zdislav', 'age' => '34'),
            $this->reader->read()
        );
        $this->assertEquals(
            array('firstname' => 'Cenek', 'lastname' => 'Wojtek', 'age' => '7'),
            $this->reader->read()
        );

        $this->assertNull($this->reader->read());
    }

    public function testInvalidCsvRead()
    {
        $this->reader->setFilePath(__DIR__ . '/../../../fixtures/invalid_import.csv');

        try {
            $this->reader->read();
        } catch (\Oro\Bundle\BatchBundle\Item\InvalidItemException $e) {
            $this->assertSame('pim_import_export.steps.csv_reader.invalid_item_columns_count', $e->getMessage());
            $parameters = $e->getMessageParameters();
            $this->assertEquals(
                array(
                    '%totalColumnsCount%',
                    '%itemColumnsCount%',
                    '%csvPath%',
                    '%lineno%',
                ),
                array_keys($parameters)
            );

            $this->assertEquals(3, $parameters['%totalColumnsCount%']);
            $this->assertEquals(4, $parameters['%itemColumnsCount%']);
            $this->assertStringEndsWith(
                'src/Pim/Bundle/ImportExportBundle/Tests/fixtures/invalid_import.csv',
                $parameters['%csvPath%']
            );
            $this->assertEquals(1, $parameters['%lineno%']);
            $this->assertNull($this->reader->read());

            return;
        }

        $this->fail('InvalidItemException was not raised');
    }

    /**
     * Test related method
     * @return StepExecution
     */
    protected function getStepExecutionMock()
    {
        return $this
            ->getMockBuilder('Oro\Bundle\BatchBundle\Entity\StepExecution')
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function getArchiverMock()
    {
        return $this->getMockBuilder('Pim\Bundle\ImportExportBundle\Archiver\InvalidItemsCsvArchiver')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
