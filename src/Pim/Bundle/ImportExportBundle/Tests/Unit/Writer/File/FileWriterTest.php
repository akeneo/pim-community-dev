<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Writer\File;

use Pim\Bundle\ImportExportBundle\Writer\File\FileWriter;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileWriterTest extends \PHPUnit_Framework_TestCase
{
    const EXPORT_DIRECTORY = '/tmp';
    const EXPORT_FILE = 'test';
    const EXPECT_PATH = '/tmp/constat';

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->writer = new FileWriter();
        $this->stepExecution = $this->getStepExecutionMock();
        $this->writer->setStepExecution($this->stepExecution);
    }
    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        @unlink(self::EXPECT_PATH);
        @unlink(sprintf('%s/%s', self::EXPORT_DIRECTORY, self::EXPORT_FILE));
    }

    /**
     * Test related method
     */
    public function testIsAConfigurableStepExecutionAwareWriter()
    {
        $this->assertInstanceOf('Oro\Bundle\BatchBundle\Item\AbstractConfigurableStepElement', $this->writer);
        $this->assertInstanceOf('Oro\Bundle\BatchBundle\Step\StepExecutionAwareInterface', $this->writer);
        $this->assertInstanceOf('Oro\Bundle\BatchBundle\Item\ItemWriterInterface', $this->writer);
    }

    /**
     * Test related method
     */
    public function testWrite()
    {
        file_put_contents(self::EXPECT_PATH, 'foo');

        $this->writer->setDirectoryName(self::EXPORT_DIRECTORY);
        $this->writer->setFileName(self::EXPORT_FILE);
        $this->writer->write(array('foo'));

        $filename = sprintf('%s/%s', self::EXPORT_DIRECTORY, self::EXPORT_FILE);
        $this->assertFileExists($filename);
        $this->assertFileEquals(self::EXPECT_PATH, $filename);
    }

    /**
     * Test related method
     */
    public function testIncrementWriteCount()
    {
        $this->writer->setDirectoryName(self::EXPORT_DIRECTORY);
        $this->writer->setFileName(self::EXPORT_FILE);

        $this->stepExecution->expects($this->exactly(2))
            ->method('incrementWriteCount');

        $this->writer->write(array('foo', 'bar'));
    }

    /**
     * @return \Oro\Bundle\BatchBundle\Entity\StepExecution
     */
    private function getStepExecutionMock()
    {
        return $this
            ->getMockBuilder('Oro\Bundle\BatchBundle\Entity\StepExecution')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
