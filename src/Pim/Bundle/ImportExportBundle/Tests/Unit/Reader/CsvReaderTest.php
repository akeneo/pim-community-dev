<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Reader;

use Pim\Bundle\ImportExportBundle\Reader\CsvReader;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
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
        $this->reader = new CsvReader();
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
        $this->reader->setFilePath(__DIR__ . '/../../fixtures/import.csv');

        $stepExecution = $this->getStepExecutionMock();
        $stepExecution
            ->expects($this->exactly(3))
            ->method('incrementReadCount');

        $this->assertEquals(
            array('firstname' => 'Severin', 'lastname' => 'Gero', 'age' => '28'),
            $this->reader->read($stepExecution)
        );
        $this->assertEquals(
            array('firstname' => 'Kyrylo', 'lastname' => 'Zdislav', 'age' => '34'),
            $this->reader->read($stepExecution)
        );
        $this->assertEquals(
            array('firstname' => 'Cenek', 'lastname' => 'Wojtek', 'age' => '7'),
            $this->reader->read($stepExecution)
        );

        $this->assertNull($this->reader->read($stepExecution));
    }

    /**
     * Test related method
     */
    public function testInvalidCsvRead()
    {
        $this->reader->setFilePath(__DIR__ . '/../../fixtures/invalid_import.csv');

        $stepExecution = $this->getStepExecutionMock();
        $stepExecution
            ->expects($this->once())
            ->method('addReaderWarning')
            ->with(
                $this->reader,
                'Expecting to have 3 columns, actually have 4.',
                array('Severin', 'Gero', '28', 'error')
            );

        $this->assertFalse($this->reader->read($stepExecution));
        $this->assertNull($this->reader->read($stepExecution));
    }

    /**
     * Test related method
     * @return StepExecution
     */
    private function getStepExecutionMock()
    {
        return $this
            ->getMockBuilder('Oro\Bundle\BatchBundle\Entity\StepExecution')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
